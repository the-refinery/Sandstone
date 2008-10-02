<?php
/*
Base Button Control Class File

@package Sandstone
@subpackage Application
*/

class ButtonBaseControl extends InputBaseControl
{

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('button_general');
		$this->_bodyStyle->AddClass('button_body');

		//Set this default style to always display inline
		$this->_bodyStyle->AddStyle("display:inline;");

        //We don't use the wrapper and message stuff.
        $this->_template->IsMasterLayoutUsed = false;

        //We always use this template
        $this->_template->FileName = "input";
    }

	/*
	PostControlValue property

	@return string
	*/
	public function getPostControlValue()
	{

		if ($this->IsCompoundControl)
		{
			//Return the PostControlValue for the child controls
			$returnValue = $this->BuildChildPostControlValues();
		}
		else
		{
			//Button controls themselves don't have a useful value to return
			$returnValue = null;
		}

		return $returnValue;
	}

    public function Render()
    {
        //Since we don't do a label tag, the label will go to the
        //button caption, as set by the "value" property
        if (is_set($this->_labelText))
        {
            $this->_defaultValue = $this->_labelText;
        }

        //Now call our parent's render method to generate the actual output.
        $returnValue =  parent::Render();

        return $returnValue;

    }

}
?>