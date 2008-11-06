<?php
/*
Hidden Control Class File

@package Sandstone
@subpackage Application
*/

class HiddenControl extends InputBaseControl
{

	public function __construct()
	{
		parent::__construct();

		$this->_inputType = "hidden";

		//Setup the default style classes
		$this->_controlStyle->AddClass('hidden_general');
		$this->_bodyStyle->AddClass('hidden_body');

        //We don't use the wrapper and message stuff.
        $this->_template->IsMasterLayoutUsed = false;

        //No matter what, we don't use a label either.
        $this->_template->FileName = "input";
	}

}
?>