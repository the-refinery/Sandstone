<?php
/**
 * Submit Button Control Class File
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

class SubmitButtonControl extends ButtonBaseControl
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "submit";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('submitbutton_general');
		$this->_bodyStyle->AddClass('submitbutton_body');

		$this->Message->BodyStyle->AddClass('submitbutton_message');
		$this->Label->BodyStyle->AddClass('submitbutton_label');

		//Default to a button caption of "Submit"
		$this->_defaultValue = "Submit";
	}
	
	protected function RenderControlBody()
	{
		$returnValue = $this->RenderInput();
		
		return $returnValue;
	}	
	
}
?>