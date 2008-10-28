<?php
/**
 * Form Name Control Class File
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

class FormNameControl extends HiddenControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "hidden";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('formname_general');
		$this->_bodyStyle->AddClass('formname_body');

		$this->Message->BodyStyle->AddClass('formname_message');
		$this->Label->BodyStyle->AddClass('formname_label');
	}


	/**
	 * Name property - Overridden here since they are always named "FormName";
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getName()
	{
		return "FormName";
	}

	public function setName($Value)
	{
		$this->_name = "FormName";
	}

	/**
	 * PostControlValue property
	 *
	 * @return string
	 */
	public function getPostControlValue()
	{
		return null;
	}
}

?>
