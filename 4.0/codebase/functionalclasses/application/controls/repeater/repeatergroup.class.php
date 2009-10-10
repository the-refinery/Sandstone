<?php
/*
Repeater Group Class File

@package Sandstone
@subpackage Application
 */

class RepeaterGroup extends ControlContainer
{
	protected $_name;
	protected $_property;
	protected $_callbackObject;
	protected $_callbackMethodName;
	protected $_groupItemCallbackMethodName;

	protected $_subGroup;

	protected $_firstElement;
	protected $_renderedChildren; 

	protected $_pendingItem;

	public function __construct($Name, $Property, $Repeater, $CallbackObject, $CallbackMethodName, $GroupItemCallbackMethodName)
	{
		parent::__construct();

		$this->_name = $Name;
		$this->_property = $Property;
		$this->_callbackObject = $CallbackObject;
		$this->_callbackMethodName = $CallbackMethodName;
		$this->_groupItemCallbackMethodName = $GroupItemCallbackMethodName;

		$this->ParentContainer = $Repeater;
		$this->_template->FileName = strtolower("{$Repeater->LocalName}_{$Name}");
		$this->_template->RequestFileType = $Repeater->Template->RequestFileType;
	}

	public function AddGroup($Name, $Property, $Repeater, $CallbackObject, $CallbackMethodName, $GroupItemCallbackMethodName)
	{
		$returnValue = false;

		if (strlen($Property) > 0)
		{
			if (is_set($this->_subGroup))
			{
				$this->_subGroup->AddGroup($Name, $Property, $Repeater, $CallbackObject, $CallbackMethodName, $GroupItemCallbackMethodName);
			}
			else
			{
				$this->_subGroup = new RepeaterGroup($Name, $Property, $Repeater, $CallbackObject, $CallbackMethodName, $GroupItemCallbackMethodName);	
			}

			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddItem($RepeaterItem)
	{
		//Is this Item part of the same group?
		if (is_set($this->_firstElement))
		{
			$compare = "\$returnValue = \$this->_firstElement->{$this->_property} == \$RepeaterItem->Element->{$this->_property};";
			eval($compare);            
		}
		else
		{
			$this->_firstElement = $RepeaterItem->Element;
			$returnValue = true;
		}


		if ($returnValue == true)
		{
			//This is part of the same group
			$command = $this->SetupGroupItemCallbackCommand();

      eval($command);
				
			if (is_set($this->_subGroup))
			{
				$isSameGroup = $this->_subGroup->AddItem($RepeaterItem);

				if ($isSameGroup == false)
				{
					$this->_renderedChildren .= $this->_subGroup->Render();
				}

			}
			else
			{
				$this->_renderedChildren .= $RepeaterItem->Render();    
			}
		}
		else
		{
			//This would be part of the next gruop
			$this->_pendingItem = $RepeaterItem;
		}

		return $returnValue;
	}

	public function Render()
	{

		$this->_template->Element = $this->_firstElement;

		//If there is a sub group, render the final one.
		if (is_set($this->_subGroup))
		{
			$this->_renderedChildren .= $this->_subGroup->Render();            
		}

		//Setup the callback function command (if any)
		$callbackCommand = $this->SetupCallbackCommand();

		//Perform any callback
		eval($callbackCommand);

		$returnValue = parent::Render();
		$returnValue = str_replace("{Content}", $this->_renderedChildren, $returnValue);       

		//Setup for the next group
		$this->_firstElement = null;
		$this->_renderedChildren = null;

		if (is_set($this->_pendingItem))
		{
			$this->_firstElement = $this->_pendingItem->Element;

			$RepeaterItem = $this->_pendingItem;
			$command = $this->SetupGroupItemCallbackCommand();
      
      eval($command);

			if (is_set($this->_subGroup))
			{
				$this->_subGroup->AddItem($this->_pendingItem);           
			}
			else
			{
				$this->_renderedChildren = $this->_pendingItem->Render();    
			}

			$this->_pendingItem = null;
		}

		return $returnValue;
	}

	protected function SetupCallbackCommand()
	{
		if (is_set($this->_callbackObject) && is_set($this->_callbackMethodName))
		{
			//Setup what we need to do.
			$returnValue = "\$this->_callbackObject->{$this->_callbackMethodName}(\$this->_firstElement, \$this->_template);";
		}

		return $returnValue;
	}

	protected function SetupGroupItemCallbackCommand()
	{
		if (is_set($this->_callbackObject) && is_set($this->_groupItemCallbackMethodName))
		{
			//Setup what we need to do.
			$returnValue = "\$this->_callbackObject->{$this->_groupItemCallbackMethodName}(\$RepeaterItem->Element, \$this->_template);";
      echo $returnValue;die();
		}

		return $returnValue;
	}


}
?>
