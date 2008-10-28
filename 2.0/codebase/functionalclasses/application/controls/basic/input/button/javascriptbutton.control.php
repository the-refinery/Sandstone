<?php
/**
 * Javascript Button Control Class File
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

class JavascriptButtonControl extends ButtonBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "button";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('javascriptbutton_general');
		$this->_bodyStyle->AddClass('javascriptbutton_body');

		$this->Message->BodyStyle->AddClass('javascriptbutton_message');
		$this->Label->BodyStyle->AddClass('javascriptbutton_label');

		//Default to a button caption of "Reset"
		$this->_defaultValue = "Go";
		
	}

	protected function RenderControlBody()
	{

		$returnValue = $this->RenderInput($onClick);

		return $returnValue;
	}


}
?>