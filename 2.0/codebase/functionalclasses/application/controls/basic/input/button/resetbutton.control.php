<?php
/**
 * Reset Button Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

class ResetButtonControl extends ButtonBaseControl
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "reset";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('resetbutton_general');
		$this->_bodyStyle->AddClass('resetbutton_body');

		$this->Message->BodyStyle->AddClass('resetbutton_message');
		$this->Label->BodyStyle->AddClass('resetbutton_label');

		//Default to a button caption of "Reset"
		$this->_defaultValue = "Reset";

	}
	
	protected function RenderControlBody()
	{
		$returnValue = $this->RenderInput();
		
		return $returnValue;
	}	
	
}
?>