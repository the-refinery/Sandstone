<?php
/*
Repeater Control Class File

@package Sandstone
@subpackage Application
*/

class RepeaterControl extends BaseControl
{

	protected $_data;
	protected $_itemIDsuffixFormat;
	protected $_repeaterItems;

	protected $_callbackObject;
	protected $_callbackMethodName;

	protected $_currentRepeaterItem;

	protected $_destroyTVsOnRender;


	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('repeater_general');
		$this->_bodyStyle->AddClass('repeater_body');

		$this->_template->IsMasterLayoutUsed = false;

	}

	/*
	Data property

	@return variant
	@param variant $Value
	 */
	public function getData()
	{
		return $this->_data;
	}

	public function setData($Value)
	{
		$this->_data = $Value;
	}

	/*
	ItemIDsuffixFormat property

	@return string
	@param string $Value
	 */
	public function getItemIDsuffixFormat()
	{
		return $this->_itemIDsuffixFormat;
	}

	public function setItemIDsuffixFormat($Value)
	{
		$this->_itemIDsuffixFormat = $Value;
	}

	/*
	RepeaterItems property

	@return array
	 */
	public function getRepeaterItems()
	{
		if (is_set($this->_repeaterItems) == false)
		{
			$this->SetupRepeaterItems();
		}

		return $this->_repeaterItems;
	}

	/*
	CurrentRepeater property

	@return RepeaterItem
	 */
	public function getCurrentRepeaterItem()
	{
		return $this->_currentRepeaterItem;
	}

	/*
	DestroyTVsOnRender property

	@return boolean
	@param boolean $Value
	 */
	public function getDestroyTVsOnRender()
	{
		return $this->_destroyTVsOnRender;
	}

	public function setDestroyTVsOnRender($Value)
	{
		$this->_destroyTVsOnRender = $Value;
	}

	public function SetCallback($Object, $MethodName)
	{
		if (is_object($Object) && strlen($MethodName) > 0)
		{
			$this->_callbackObject = $Object;
			$this->_callbackMethodName = $MethodName;
		}
	}

	public function ClearCallBack()
	{
		$this->_callbackObject = null;
		$this->_callbackMethodName = null;
	}

	protected function SetupRepeaterItems()
	{

		if(is_set($this->_data))
		{
			$this->_repeaterItems = Array();

    		//What should we be looping over?
			if ($this->_data instanceof ObjectSet)
			{
				if ($this->_data->Count > 0)
				{
					$elements = $this->_data->ItemsByIndex;
				}
				else
				{
					//Nothing in our objectset, so give an empty array
					$elements = Array();
				}
			}
			else
			{
				$elements = $this->_data;
			}

			foreach ($elements as $currentElement)
			{
				//Build a Repeater Item
				$this->_repeaterItems[] = $this->CreateRepeaterItem($currentElement, count($this->_repeaterItems) + 1);
			}

		}
		else
		{
			$this->_repeaterItems = null;
		}

	}

	protected function CreateRepeaterItem($CurrentElement, $ElementIncrement)
	{

		//Setup a repeater item
		$returnValue = new RepeaterItem();
		$returnValue->ParentContainer = $this;
		$returnValue->Element = $CurrentElement;

		$returnValue->Template->FileName = strtolower($this->LocalName) . "_item";
		$returnValue->Template->RequestFileType = $this->_template->RequestFileType;

		//What should the item's name be?
		if (is_set($this->_itemIDsuffixFormat))
		{
			$idSuffix = $this->ParseIDsuffixFormat($returnValue);

			if (strlen($idSuffix) == 0)
			{
				$idSuffix = $ElementIncrement;
			}
		}
		else
		{
			$idSuffix = $ElementIncrement;
		}

		//Set it's name
		$returnValue->Name = "Item_{$idSuffix}";

		//Add the template variables we need
		$returnValue->Template->CloneTemplateVariables($this->_template);

		return $returnValue;
	}

	protected function ParseIDsuffixFormat($CurrentRepeaterItem)
	{

		$returnValue = $this->_itemIDsuffixFormat;

		$pattern = "/(\{)([A-Za-z0-9]+)(\})/";
		preg_match_all($pattern, $this->_itemIDsuffixFormat, $properties, PREG_SET_ORDER);

		foreach ($properties as $tempProperty)
		{
        	$tempToken = $tempProperty[0];
            $tempPropertyName = $tempProperty[2];

			if ($CurrentRepeaterItem->Element->HasProperty($tempPropertyName))
			{
				$replaceValue = $CurrentRepeaterItem->Element->$tempPropertyName;
			}
			else
			{
				$replaceValue = "";
			}

			$returnValue = str_replace($tempToken, $replaceValue, $returnValue);

		}

		return $returnValue;
	}

	public function Render()
	{

		if (count($this->RepeaterItems) > 0)
		{
			$returnValue = parent::Render();

			//Setup the callback function command (if any)
			$callbackCommand = $this->SetupCallbackCommand();

			foreach ($this->RepeaterItems as $tempRepeaterItem)
			{
				$tempRepeaterItem->DestroyTVsOnRender = $this->_destroyTVsOnRender;

				$this->_currentRepeaterItem = $tempRepeaterItem;

				//Perform any callback
				eval($callbackCommand);

				//Render it
				$itemContent .= $tempRepeaterItem->Render();
			}

			$returnValue = str_replace("{Content}", $itemContent, $returnValue);
		}
		else
		{
			//No Data Found
			if (is_set($this->_template->FileName) == false)
			{
				$this->_template->FileName = strtolower("{$this->LocalName}_nodata");
			}

			$returnValue = parent::Render();
		}

		if ($this->_destroyTVsOnRender)
		{
			$this->_template->DestroyTemplateVariables();
		}

		return $returnValue;
	}

	protected function SetupCallbackCommand()
	{
        if (is_set($this->_callbackObject))
		{
			//Setup what we need to do.
			$returnValue = "\$this->_callbackObject->{$this->_callbackMethodName}(\$tempRepeaterItem->Element, \$tempRepeaterItem->Template);";
		}

		return $returnValue;
	}

	public function RenderObservers($Javascript)
	{

		//Find any "On-X" functions in our passed Javascript.
		$pattern = "/function {$this->Name}_Item_([A-Za-z0-9]+)_On([A-Za-z]+)\(.*\)/";
		preg_match_all($pattern, $Javascript, $functions, PREG_SET_ORDER);

		//Did we find any?
		if (count($functions) > 0)
		{
			//We have some, so register the observers
			foreach ($functions as $tempFunction)
			{

				$eventName = strtolower($tempFunction[2]);
				$endOfFunctionName = strpos($tempFunction[0], "(");
				$functionName = substr(substr($tempFunction[0], 0, $endOfFunctionName), 9);

				if (is_array($this->RepeaterItems) && count($this->RepeaterItems) > 0)
				{
					foreach ($this->RepeaterItems as $tempRepeaterItem)
					{
						$targetDOMelementID= $tempRepeaterItem->Name . "_" . $tempFunction[1];

						//(check in JS on the client side to make sure the DOM element exists)
						$returnValue .= "\tif (\$('{$targetDOMelementID}'))\n";
						$returnValue .= "\t{\n";
						$returnValue .= "\t\tEvent.observe('{$targetDOMelementID}', '{$eventName}', {$functionName});\n";
						$returnValue .= "\t}\n";
					}

				}
			}
		}

		if (is_array($this->RepeaterItems) && count($this->RepeaterItems) > 0)
		{
			foreach($this->RepeaterItems as $tempRepeaterItem)
			{
				$returnValue .= $tempRepeaterItem->RenderObservers($Javascript);
			}
		}

		return $returnValue;
	}

}
?>
