<?php
/**
 * Page Form Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class PageForm extends ControlContainer
{
	protected $_target;

	protected $_encType;

	public function __construct($EventParameters)
	{
		parent::__construct();

		$this->_eventParameters = $EventParameters;

		//Set some default values
		$this->_encType = "multipart/form-data";

		$this->Buffer = new DIVcontrol();
		$this->Buffer->IsManuallyRendered = true;

		//Since this div is only for use as the AJAX return buffer, we'll take off
		//the normal control classes
		$this->Buffer->BodyStyle->RemoveClass('control_body');
		$this->Buffer->BodyStyle->RemoveClass('div_body');

		//And set it to not display at all.
		$this->Buffer->BodyStyle->AddStyle("display: none;");

		$this->_isRawValuePosted = false;
	}

	public function __toString()
	{
		//Start the form
		$returnValue = $this->Begin;

		//If there are any controls, render them
     	if (count($this->_controlOrder) > 0)
		{
			$returnValue .= $this->RenderControls();
		}

		//Close the form
		$returnValue .= $this->End;

		return $returnValue;
	}

	/**
	 * Name property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function setName($Value)
	{

		parent::setName($Value);

		//Set a hidden control for this value
		$this->FormName = new FormNameControl();
		$this->FormName->DefaultValue = $Value;

	}

	/**
	 * Target property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getTarget()
	{
		return $this->_target;
	}

	public function setTarget($Value)
	{
		$this->_target = $Value;
	}

	/**
	 * Begin property
	 * 
	 * @return string
	 */	
	public function getBegin()
	{
		$action = "action=\"{$this->Page->RequestedURL}\"";
		$method = "method=\"post\"";

		if (is_set($this->_target))
		{
			$target = "target=\"{$this->_target}\"";
		}

		//Loop through the controls and see if we have a file control
		foreach ($this->AllActiveControls as $tempControl)
		{
			if ($tempControl instanceof FileControl)
			{
				$foundFileControl = true;
			}
		}
		
		//If we have a file control, add the ENC type
		if ($foundFileControl)
		{
			$encType = "enctype=\"{$this->_encType}\"";
		}

		$returnValue = "<form {$action} {$method} {$target} {$encType} {$this->_JS->CallList}>";
		
		return $returnValue;		
	}

	/**
	 * End property
	 * 
	 * @return string
	 */
	public function getEnd()
	{

		//Dump the buffer
		$returnValue = $this->Buffer->__toString();

		$returnValue .= "</form>";
		
		return $returnValue;
	}


}
?>