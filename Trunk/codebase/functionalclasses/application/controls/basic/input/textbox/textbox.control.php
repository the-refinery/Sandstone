<?php
/*
TextBox Control Class File

@package Sandstone
@subpackage Application
*/

class TextBoxControl extends InputBaseControl
{
	protected $_size;
	protected $_maxLength;

	public function __construct()
	{
		parent::__construct();

        $this->_inputType = "text";

		//Setup the default style classes
		$this->_controlStyle->AddClass('textbox_general');
		$this->_bodyStyle->AddClass('textbox_body');
	}

	/*
	Size property

	@return int
	@param int $Value
	*/
	public function getSize()
	{
		return $this->_size;
	}

	public function setSize($Value)
	{
		$this->_size = $Value;
	}

	/*
	MaxLength property

	@return int
	@param int $Value
	*/
	public function getMaxLength()
	{
		return $this->_maxLength;
	}

	public function setMaxLength($Value)
	{
		$this->_maxLength = $Value;
	}

	public function Render()
	{

		//Has a size been defined?
		if (is_set($this->_size) && is_numeric($this->_size))
		{
			$parameters .= "size=\"{$this->_size}\" ";
		}

		//Has a max length been defined?
		if (is_set($this->_maxLength) && is_numeric($this->_maxLength))
		{
			$parameters .= "maxlength=\"{$this->_maxLength}\" ";
		}

		$this->_template->Parameters = $parameters;

        //Now call our parent's render method to generate the actual output.
        $returnValue =  parent::Render();

        return $returnValue;
	}

}
?>