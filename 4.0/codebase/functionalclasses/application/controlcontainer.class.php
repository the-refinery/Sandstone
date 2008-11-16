<?php
/*
Control Container Class File

@package Sandstone
@subpackage Application
*/

class ControlContainer extends Renderable
{

	protected $_name;

	protected $_parentContainer;

   	protected $_controls;
	protected $_controlOrder;

	protected $_activeControls;
	protected $_staticControls;
	protected $_hiddenControls;

    protected $_isControlJavascriptSetup;

	protected $_eventParameters;

	protected $_isRawValuePosted;

	public function __construct()
	{

		parent::__construct();

		$this->_controls = new DIarray();
		$this->_staticControls = new DIarray();
		$this->_hiddenControls = new DIarray();
		$this->_controlOrder = new DIarray();

		$this->_eventParameters = Array();

		$this->_isRawValuePosted = true;

	}

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue =  $this->$getter();
		}
		else if (array_key_exists(strtolower($Name), $this->_controls))
		{
			$returnValue = $this->_controls[strtolower($Name)];
		}
		else if (array_key_exists(strtolower($Name), $this->_hiddenControls))
		{
			$returnValue = $this->_hiddenControls[strtolower($Name)];
		}
		else if (array_key_exists(strtolower($Name), $this->_staticControls))
		{
			$returnValue = $this->_staticControls[strtolower($Name)];
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}

		return $returnValue;
	}

	public function __set($Name,$Value)
	{
		$setter='set'.$Name;

		if(method_exists($this,$setter))
		{
			$this->$setter($Value);
		}
		else if ($Value instanceof BaseControl)
		{
			//This is a control we should add to our control array
			$Value->Name = $Name;
			$Value->ParentContainer = $this;
			$Value->EventParameters = $this->_eventParameters;
			$Value->Template->RequestFileType = $this->_template->RequestFileType;

			//Make sure we don't have any existing controls of this name.
			unset($this->_activeControls[strtolower($Name)]);
			unset($this->_staticControls[strtolower($Name)]);
			unset($this->_hiddenControls[strtolower($Name)]);

			//Add this to the master control array
			$this->_controls[strtolower($Name)] = $Value;

			//Which control type array should we use?
			if ($Value instanceof StaticBaseControl)
			{
				$this->_staticControls[strtolower($Name)] = $Value;
			}
			else if ($Value instanceof HiddenControl)
			{
				$this->_hiddenControls[strtolower($Name)] = $Value;
			}
			else
			{
				$this->_activeControls[strtolower($Name)] = $Value;
			}

			//Save the order this control was added, so for automatic rendering, they are
			//displayed in the order they were added.
			$this->_controlOrder[] = $Value;

		}
		else if(method_exists($this,'get'.$Name))
		{
			throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
		}
		else
		{
			throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
		}
	}

	/**
	Name property

	@return string
	@param string $Value
	*/
	public function getName()
	{

		if (is_set($this->_parentContainer))
		{
			//We have a parent, does it have a name?
			$parentName = $this->_parentContainer->Name;

			if (is_set($parentName))
			{
				$returnValue = $parentName . "_" . $this->_name;
			}
			else
			{
				$returnValue = $this->_name;
			}
		}
		else
		{
			//This doesn't have a parent, is it a page?
			if ($this instanceof BasePage)
			{
				//It's a page, so we don't return anything because we don't want to prefix
				//forms or controls with the page's name:
				//MainForm_FirstNameTextBox rather than Foo_MainForm_FirstNameTextBox
				$returnValue = null;
			}
			else
			{
	            //It's not a page, so we just return it's name
				$returnValue = $this->_name;
			}
		}

		return $returnValue;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/*
	Local Name property

	@return string
	*/
	public function getLocalName()
	{
		return $this->_name;
	}

	/*
	ParentContainer property

	@return ControlContainer
	@param ControlContainer $Value
	*/
	public function getParentContainer()
	{
		return $this->_parentContainer;
	}

	public function setParentContainer($Value)
	{
		if ($Value instanceof ControlContainer)
		{
			$this->_parentContainer = $Value;
		}
		else
		{
			$this->_parentContainer = null;
		}
	}

	/*
	Controls property

	@return array
	*/
	public function getControls()
	{
		return $this->_controls;
	}

	/*
	ActiveControls property

	@return Array
	*/
	public function getActiveControls()
	{
		return $this->_activeControls;
	}

	/*
	StaticControls property

	@return Array
	*/
	public function getStaticControls()
	{
		return $this->_staticControls;
	}

	/*
	HiddenControls property

	@return array
	*/
	public function getHiddenControls()
	{
		return $this->_hiddenControls;
	}

	/*
	EventParameters property

	@return Array
	@param Array $Value
	*/
	public function getEventParameters()
	{
		return $this->_eventParameters;
	}

	public function setEventParameters($Value)
	{
		if (is_array($Value))
		{
			$this->_eventParameters = $Value;
		}
		else
		{
			$this->_eventParameters = Array();
		}

		//Loop any existing controls and pass down the current EP
		foreach ($this->_controls as $tempControl)
		{
			$tempControl->EventParameters = $this->_eventParameters;
		}

		//Now locally parse the EPs
		$this->ParseEventParameters();

	}

	/*
	Page property

	@return Page
	@param Page $Value
	*/
	public function getPage()
	{
		$returnValue = $this;

		while (($returnValue instanceof BasePage) == false)
		{
			$returnValue = $returnValue->ParentContainer;
		}

		return $returnValue;
	}

   	/*
	Form property

	@return PageForm
	@param PageForm $Value
	*/
	public function getForm()
	{
		$returnValue = $this;

		while (is_set($returnValue) && ($returnValue instanceof PageForm) == false)
		{
			$returnValue = $returnValue->ParentContainer;
		}

		return $returnValue;

	}

	/*
	AllActiveControls property

	@return array
	*/
	public function getAllActiveControls()
	{
		$returnValue = Array();

		//Loop through the controls
		foreach($this->_controls as $tempTopLevelControl)
		{

			if (($tempTopLevelControl instanceof HiddenControl) == false && ($tempTopLevelControl instanceof StaticBaseControl == false))
			{
				//Add this top level control to our return array
				$returnValue[$tempTopLevelControl->Name] = $tempTopLevelControl;
			}

			//Now add any of it's children
			$returnValue = array_merge($returnValue, $tempTopLevelControl->AllActiveControls);
		}

		return $returnValue;
	}

	/*
	PostControlValue property

	@return string
	*/
	public function getPostControlValue()
	{

		if (count($this->_controls) > 0)
		{
			//Post the values of the child controls.
            foreach($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->PostControlValue;
			}
		}

		if ($this->_isRawValuePosted)
		{
			//Post my value
			$returnValue = "+'&{$this->Name}='+escape(\$F('{$this->Name}'))";
		}

		return $returnValue;
	}

	/*
	PostHiddenControlValue property

	@return string
	*/
	public function getPostHiddenControlValues()
	{
		foreach($this->_hiddenControls as $tempControl)
		{
			$returnValue .= $tempControl->PostControlValue;
		}

		return $returnValue;
	}

	/*
	ControlValueSnippet property

	@return string
	*/
	public function getControlValueSnippet()
	{
		if (count($this->_activeControls) > 0 || count($this->_hiddenControls) > 0)
		{
			//Post the value snippets of the child controls.
         	foreach($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->ControlValueSnippet . "\n";
			}

		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

    protected function SetupControlJavascript()
    {
        $this->_isControlJavascriptSetup = true;
	}

	protected function ParseEventParameters()
	{

	}

	protected function BuildChildPostControlValues()
	{

		foreach($this->_controls as $tempControl)
		{
			$returnValue .= $tempControl->PostControlValue;
		}

		return $returnValue;
	}

	protected function ReleaseControls()
	{

		foreach ($this->_controls as $tempControl)
		{
			if ($tempControl instanceof ControlContainer)
			{
				$tempControl->ReleaseControls();
			}
		}

		$this->_controls = new DIarray();
		$this->_staticControls = new DIarray();
		$this->_hiddenControls = new DIarray();
		$this->_controlOrder = new DIarray();

		$this->_eventParameters = Array();
	}

	public function RenderObservers($Javascript)
	{

		//Find any "On-X" functions in our passed Javascript.
		$pattern = "/function {$this->Name}_On([A-Za-z]+)\(.*\)/";
		preg_match_all($pattern, $Javascript, $functions, PREG_SET_ORDER);

		//Did we find any?
		if (count($functions) == 1)
		{
			$function = $functions[0];
			
			$returnValue = "\tif (\$('{$this->Name}')) ";
						
			$eventName = strtolower($function[1]);
			$endOfFunctionName = strpos($function[0], "(");
			$functionName = substr(substr($function[0], 0, $endOfFunctionName), 9);

			if ($eventName == "load")
			{
				$returnValue .= "{$functionName}();\n";
			}
			else
			{
				$returnValue .= "\$('{$this->Name}').observe('{$eventName}', {$functionName});\n";
			}
		}
		elseif (count($functions) > 1)
		{
			//We have some, so register the observers
			//(check in JS on the client side to make sure the DOM elements exist)
			$returnValue = "\tif (\$('{$this->Name}'))\n";
			$returnValue .= "\t{\n";

			foreach ($functions as $tempFunction)
			{
				$eventName = strtolower($tempFunction[1]);
				$endOfFunctionName = strpos($tempFunction[0], "(");
				$functionName = substr(substr($tempFunction[0], 0, $endOfFunctionName), 9);

				if ($eventName == "load")
				{
					$returnValue .= "\t\t{$functionName}();\n";
				}
				else
				{
					$returnValue .= "\t\t\$('{$this->Name}').observe('{$eventName}', {$functionName});\n";
				}
			}

			$returnValue .= "\t}\n";
		}

		//Now look for any "sub elements" of this control which aren't part of our controls array
		$pattern = "/function {$this->Name}_([A-Za-z]+)_On([A-Za-z]+)\(.*\)/";
		preg_match_all($pattern, $Javascript, $functions, PREG_SET_ORDER);

		//Did we find any?
		if (count($functions) > 0)
		{
			//We have some, so register the observers
			foreach ($functions as $tempFunction)
			{
				$elementName = $tempFunction[1];
				$eventName = strtolower($tempFunction[2]);
				$endOfFunctionName = strpos($tempFunction[0], "(");
				$functionName = substr(substr($tempFunction[0], 0, $endOfFunctionName), 9);

				//Is this element a control?
				if (array_key_exists(strtolower($elementName), $this->Controls) == false)
				{
					//(check in JS on the client side to make sure the DOM elements exist)
					$returnValue .= "\tif (\$('{$this->Name}_{$elementName}')) ";
					$returnValue .= "\$('{$this->Name}_{$elementName}').observe('{$eventName}', {$functionName});\n";
				}

			}
		}

		//Now Loop any controls and append thier Observers
		if (count($this->_controls) > 0)
		{
			foreach ($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->RenderObservers($Javascript);
			}
		}

		return $returnValue;
	}
}
?>
