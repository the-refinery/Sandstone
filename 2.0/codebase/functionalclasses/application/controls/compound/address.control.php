<?php
/**
 * Address Control Class File
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

class AddressControl extends BaseControl
{

	protected $_defaultValue;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('address_general');
		$this->_bodyStyle->AddClass('address_body');

		$this->Message->BodyStyle->AddClass('address_message');
		$this->Label->BodyStyle->AddClass('address_label');

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;
	}

	/**
	 * DefaultValue property
	 *
	 * @return address
	 *
	 * @param address $Value
	 */
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		if ($Value instanceof Address && $Value->IsLoaded)
		{
			$this->_defaultValue = $Value;

			$this->Street->DefaultValue = $Value->Street;
			$this->CityState->InnerHTML = "{$Value->ZipCode->City}, {$Value->ZipCode->State->Name}";
			$this->Zip->DefaultValue = $Value->ZipCode->ZipCode;
		}
		else
		{
			$this->_defaultValue = null;
			$this->Street->DefaultValue = null;
			$this->CityState->InnerHTML = "City, State";
			$this->Zip->DefaultValue = null;
		}
	}

	protected function ParseEventParameters()
	{

		if (is_set($this->Street->Value) && is_set($this->Zip->Value))
		{
			$this->_value = new Address();
			$this->_value->Street = $this->Street->Value;
			$this->_value->ZipCode = new ZipCode($this->Zip->Value);
		}
		else
		{
			$this->_value = null;
		}
	}

	protected function RenderControlBody()
	{
		$returnValue = $this->RenderLabel();
		$returnValue .= $this->Street->__toString();
		$returnValue .= $this->CityState->__toString();
		$returnValue .= $this->Zip->__toString();

		return $returnValue;
	}

	protected function RenderLabel()
	{

		$this->Label->TargetControlName = $this->Street->Name;

		$returnValue = $this->Label->__toString();

		return $returnValue;
	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->Street = new TextAreaControl();
		$this->Street->ControlStyle->AddClass("address_streetitem");
		$this->Street->Rows = 2;
		$this->Street->Columns = 30;
		$this->Street->Effects->Scope = $this->_effects->Scope;

		$this->CityState = new DIVcontrol();
		$this->CityState->BodyStyle->AddClass("address_citystateitem");
		$this->CityState->BodyStyle->AddStyle("display: inline;");
		$this->CityState->BodyStyle->AddStyle("margin-right: 1em;");
		$this->CityState->InnerHTML = "City, State";
		$this->CityState->Effects->Scope = $this->_effects->Scope;

   		$this->Zip= new TitleTextBoxControl();
		$this->Zip->ControlStyle->AddClass("address_zipitem");
		$this->Zip->ControlStyle->AddStyle("display: inline;");
		$this->Zip->Size = 6;
		$this->Zip->Label->Text = "Zip";
		$this->Zip->Effects->Scope = $this->_effects->Scope;
	}

	protected function SetupControlJavascript()
    {

    	$this->Zip->JS->OnBlur->AddControlEvent("Autocomplete", false, $this);

        parent::SetupControlJavascript();

    }

	protected function AutoComplete_Handler($EventParameters)
	{
		$returnValue = new EventResults();

		//Attempt to load a ZipCode object with the passed data.
		$tempZip = new Zipcode($this->Zip->Value);

		if ($tempZip->IsLoaded)
		{
			$this->_validationMessage = null;
			$this->CityState->InnerHTML = "{$tempZip->City}, {$tempZip->State->Name}";
		}
		else
		{
			$this->_validationMessage = "Invalid ZipCode";
			$this->CityState->InnerHTML = "City, State";
		}

		//Update the city/state div
		echo $this->CityState->Effects->InnerHTMLblock;

		//Handle our message
		echo $this->ValidationJavascript;

		$returnValue->Value = $tempZip->IsLoaded;
		$returnValue->Complete();

		return $returnValue;

	}

}
?>
