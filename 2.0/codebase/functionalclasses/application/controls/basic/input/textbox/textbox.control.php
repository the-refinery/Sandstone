<?php
/**
 * TextBox Control Class File
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

class TextBoxControl extends InputBaseControl
{
	protected $_size;
	protected $_maxLength;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_inputType = "text";
		$this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('textbox_general');
		$this->_bodyStyle->AddClass('textbox_body');

		$this->Message->BodyStyle->AddClass('textbox_message');
		$this->Label->BodyStyle->AddClass('textbox_label');

	}

	/**
	 * Size property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getSize()
	{
		return $this->_size;
	}

	public function setSize($Value)
	{
		$this->_size = $Value;
	}
	
	/**
	 * MaxLength property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getMaxLength()
	{
		return $this->_maxLength;
	}

	public function setMaxLength($Value)
	{
		$this->_maxLength = $Value;
	}
	
	protected function RenderControlBody()
	{
		
		//Define the label
		$returnValue = $this->RenderLabel();
		
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
		
		$returnValue .= $this->RenderInput($parameters);

		return $returnValue;
	}	
	
}
?>