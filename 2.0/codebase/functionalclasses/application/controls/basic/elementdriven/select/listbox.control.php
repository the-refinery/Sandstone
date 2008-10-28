<?php
/**
 * ListBox Control Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

class ListBoxControl extends SelectBaseControl
{

	public function __construct()
	{
		parent::__construct();
		
		$this->_isMultiselect = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('listbox_general');
		$this->_bodyStyle->AddClass('listbox_body');

		$this->Message->BodyStyle->AddClass('listbox_message');
		$this->Label->BodyStyle->AddClass('listbox_label');


	}
					
}
?>