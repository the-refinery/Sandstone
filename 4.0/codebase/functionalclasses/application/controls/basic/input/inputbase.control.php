<?php
/*
Input Base Control Class File

@package Sandstone
@subpackage Application
*/

class InputBaseControl extends BaseControl
{

    protected $_inputType;

	protected $_defaultValue;

	protected $_isValueReturned;

	public function __construct()
	{
		parent::__construct();

        $this->_isValueReturned = true;

		//Setup the default style classes
		$this->_controlStyle->AddClass('input_general');
		$this->_bodyStyle->AddClass('input_body');
    }

	/*
	DefaultValue property

	@return variant
	@param variant $Value
	*/
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		$this->_defaultValue = $Value;
	}

	/*
	Value property

	@return variant
	*/
	public function getValue()
	{
		if (is_set($this->_value))
		{
			$returnValue = $this->_value;
		}

		return $returnValue;
	}

	protected function RenderDisplayValue()
	{

		//Do we have a value?
		if (is_set($this->_value) && $this->_isValueReturned)
		{
			$returnValue = DIescape($this->_value);
		}
		else
		{
			//Do we have a default value?
			if (is_set($this->_defaultValue) && $this->_isValueReturned)
			{
				$returnValue = DIescape($this->_defaultValue);
			}
			else
			{
				$returnValue = "";
			}
		}


		return $returnValue;

	}

	public function Render()
	{

		//Set some template variables
        $this->_template->Type = $this->_inputType;
		$this->_template->InputValue = "value=\"{$this->RenderDisplayValue()}\"";

        //Do we need to pick a template still?
        if (is_set($this->_template->FileName) == false)
        {
            //Which template should we use?
            if (is_set($this->_labelText))
            {
                $this->_template->FileName = "inputandlabel";
            }
            else
            {
                $this->_template->FileName = "input";
            }
        }

        //Now call our parent's render method to generate the actual output.
        $returnValue =  parent::Render();

        return $returnValue;
	}

}
?>