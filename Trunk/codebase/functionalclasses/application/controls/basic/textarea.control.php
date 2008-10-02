<?php
/*
TextArea Control Class File

@package Sandstone
@subpackage Application
*/

class TextAreaControl extends BaseControl
{
	protected $_defaultValue;

	protected $_rows;
	protected $_columns;

	protected $_isValueReturned;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('textarea_general');
		$this->_bodyStyle->AddClass('textarea_body');
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
	Rows property

	@return int
	@param int $Value
	*/
	public function getRows()
	{
		return $this->_rows;
	}

	public function setRows($Value)
	{
		$this->_rows = $Value;
	}

	/*
	Columns property

	@return int
	@param int $Value
	*/
	public function getColumns()
	{
		return $this->_columns;
	}

	public function setColumns($Value)
	{
		$this->_columns = $Value;
	}

    public function Render()
    {
        //Do we have Rows defined?
        if (is_set($this->_rows) && $this->_rows > 0)
        {
            $this->_template->Rows = "rows=\"{$this->_rows}\"";
        }

        //Do we have columns defined?
        if (is_set($this->_columns) && $this->_columns > 0)
        {
            $this->_template->Cols = "cols=\"{$this->_columns}\"";
        }

        //Do we have a value?
        if (is_set($this->_value))
        {
            $this->_template->Value = $this->_value;
        }
        else
        {
            //Do we have a default value?
            if (is_set($this->_defaultValue))
            {
               $this->_template->Value = $this->_defaultValue;
            }
        }

        //Now call our parent's render method to generate the actual output.
        $returnValue =  parent::Render();

        return $returnValue;

    }

}
?>