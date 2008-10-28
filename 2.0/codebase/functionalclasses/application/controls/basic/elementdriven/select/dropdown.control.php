<?php
/**
 * Dropdown Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class DropdownControl extends SelectBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		$this->_isMultiselect = false;

		//Setup the default style classes
		$this->_controlStyle->AddClass('dropdown_general');
		$this->_bodyStyle->AddClass('dropdown_body');

		$this->Message->BodyStyle->AddClass('dropdown_message');
		$this->Label->BodyStyle->AddClass('dropdown_label');

	}
					
}
?>