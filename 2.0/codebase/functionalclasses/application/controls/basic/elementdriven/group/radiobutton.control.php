<?php
/**
 * Radio Button Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class RadioButtonControl extends GroupBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "radio";

		//Setup the default style classes
		$this->_controlStyle->AddClass('radiobutton_general');
		$this->_bodyStyle->AddClass('radiobutton_body');

		$this->Message->BodyStyle->AddClass('radiobutton_message');
		$this->Label->BodyStyle->AddClass('radiobutton_label');

	}

    /**
	 * HighlightDOMids property
	 *
	 * @return array
	 */
	public function getHighlightDOMids()
	{
		$returnValue = Array();

		$returnValue[] = $this->UL->Name;

		return $returnValue;
	}


	/**
	 * InputName property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getInputName()
	{

		$returnValue = $this->_name;

		return $returnValue;
	}

}
?>