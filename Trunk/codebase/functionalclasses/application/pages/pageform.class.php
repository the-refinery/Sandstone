<?php
/*
Page Form Class File

@package Sandstone
@subpackage Application
*/

class PageForm extends ControlContainer
{
	protected $_target;

	protected $_encType;

	protected $_redirectTarget;

	public function __construct($EventParameters)
	{
		parent::__construct();

		$this->_eventParameters = $EventParameters;

		// Set some default values
		$this->_encType = "multipart/form-data";

		$this->_isRawValuePosted = false;

        //Prep our template to use the form layout
        $this->_template->IsMasterLayoutUsed = true;
        $this->_template->MasterLayoutFileName = "form";

	}

	/*
	Target property

	@return string
	@param string $Value
	*/
	public function getTarget()
	{
		return $this->_target;
	}

	public function setTarget($Value)
	{
		$this->_target = $Value;
    }

	/*
	RedirectTarget property

	@return string
	@param string $Value
	 */
	public function getRedirectTarget()
	{
		return $this->_redirectTarget;
	}

	public function setRedirectTarget($Value)
	{
		$this->_redirectTarget = $Value;
	}

    public function Render()
    {

        $this->_template->FormName = $this->_name;
        $this->_template->RequestedURL = Routing::GetRequestedURL();

        if (is_set($this->_target))
        {
            $this->_template->Target = "target=\"{$this->_target}\"";
        }

        //Loop through the controls and see if we have a file control
        foreach ($this->AllActiveControls as $tempControl)
        {
            if ($tempControl instanceof FileControl)
            {
            	//We have a file control, add the ENC type
                $this->_template->EncType = "enctype=\"multipart/form-data\"";
            }
        }

        //Now call our parent's render method to generate the actual output.
        $returnValue =  parent::Render();

        return $returnValue;

    }

}
?>