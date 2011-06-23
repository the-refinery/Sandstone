<?php
/**
 * Smarty Class Extension File
 * 
 * @package Sandstone
 * @subpackage Smarty
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.Application.Pages");
SandstoneNamespace::Using("Sandstone.Application.Controls");
SandstoneNamespace::Using("Sandstone.Markdown");

class DISmarty extends Smarty
{
	
	protected $_forms;
	protected $_controls;
	protected $_controlAreaHTML;

	protected $_helperFunctions;
	protected $_actions;

	protected $_templateVariables = Array();
	
	// Let you set smarty variables like properties
	public function __set($Name, $Value)
	{
		switch (strtolower($Name))
		{
			case "forms":
				$this->_forms = $Value;
				break;

			case "controls":
				$this->_controls = $Value;
				break;

			case "controlarea":
				$this->_controlAreaHTML = $Value;
				break;

			default:
				$this->_templateVariables[$Name] = $Value;
				break;
		}
	}
	
	public function __get($Name)
	{
		
		switch (strtolower($Name))
		{
			case "forms":
				$returnValue = $this->_forms;
				break;

			case "controls":
				$returnValue = $this->_controls;
				break;

			case "controlarea":
				$returnValue = $this->_controlAreaHTML;
				break;

			default:
				$returnValue = $this->_templateVariables[$Name];
				break;
		}
		
		return $returnValue;
	}
	
	public function display($TemplateFileName)
	{		
		//Set the general template variables
		foreach($this->_templateVariables as $name=>$value)
		{
			$value = DIescape($value);
			$this->assign($name, $value);
		}
		
		// Some Template Variables shouldn't be escaped
		$this->assign("OnLoad", $this->_templateVariables['OnLoad']);
		
		//Set the Forms
		if(count($this->_forms) > 0)
		{
			foreach ($this->_forms as $tempForm)
			{
				$this->assign($tempForm->Name, $tempForm);
				
				foreach($tempForm->Controls as $tempControl)
				{
					$this->assign("{$tempControl->Name}", $tempControl);
				}
			}			
		}

		//Set the Controls
		if (count($this->_controls) > 0)
		{
			foreach($this->_controls as $tempControl)
			{
				$this->assign("{$tempControl->Name}", $tempControl);
			}
		}

		// Add Template Helpers
		$this->_helperFunctions = new SmartyHelper();
		$this->assign('Helper', $this->_helperFunctions);
		
		// Add Actions
		$this->_actions = new SmartyAction();
		$this->assign('Action', $this->_actions);
		
		$this->AddShortcutSyntax();

		parent::display($TemplateFileName);
	}	
	
	protected function AddShortcutSyntax()
	{
		$this->assign('H', $this->_helperFunctions); // Helper Shortcut
		$this->assign('A', $this->_actions); // Actions Shortcut
		
		// Prototype & Scriptaculous
		$this->assign('Prototype', new Prototype);
		$this->assign('Script', new Scriptaculous);		
	}
}

?>