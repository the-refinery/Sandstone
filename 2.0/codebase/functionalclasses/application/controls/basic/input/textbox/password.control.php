<?php
/**
 * Password Control Class File
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

class PasswordControl extends TextBoxControl
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "password";
		$this->_isValueReturned = false;

        //Setup the default style classes
        $this->_controlStyle->AddClass('password_general');
        $this->_bodyStyle->AddClass('password_body');

        $this->Message->BodyStyle->AddClass('password_message');
        $this->Label->BodyStyle->AddClass('password_label');

	}
	

}
?>