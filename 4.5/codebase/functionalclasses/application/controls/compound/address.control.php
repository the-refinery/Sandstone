<?php
/*
Address Control Class File

@package Sandstone
@subpackage Application
*/

SandstoneNamespace::Using("Sandstone.Utilities.String");
SandstoneNamespace::Using("Sandstone.Address");

class AddressControl extends BaseControl
{
	const US_ONLY = 1;
	const US_CANADA = 2;

	protected $_countryMode;

	protected $_defaultValue;

	public function __construct()
	{
		parent::__construct();

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;

		$this->_countryMode = self::US_ONLY;	

	}

	public function getCountryMode()
	{
		return $this->_countryMode;
	}

	public function setCountryMode($Value)
	{
		if ($Value > 0 && $Value < 3)
		{
			$this->_countryMode = $Value;
		}
		else
		{
			$this->_countryMode = self::US_ONLY;	
		}
	}

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
			$this->City->DefaultValue = $Value->City;
			$this->State->DefaultValue = $Value->ProvinceCode;
			$this->Zip->DefaultValue = $Value->PostalCode;
			$this->Country->SelectElement($Value->CountryCode);

			//if we have a value built already, check to see if it's the same as
			$isSameAddress = $Value->IsSameAddress($this->_value);

			if ($isSameAddress)
			{
				$this->_value = $Value;
			}

		}
		else
		{
			$this->_defaultValue = null;

			$this->Street->DefaultValue = null;
			$this->City->DefaultValue = null;
			$this->State->DefaultValue = null;
			$this->Zip->DefaultValue = null;
		}
	}

	protected function ParseEventParameters()
	{

		$isValid = $this->ValidateParts();

		if ($isValid)
		{
			$this->_value = new Address();
			$this->_value->Street = $this->Street->Value;
			$this->_value->City = $this->City->Value;
			$this->_value->ProvinceCode = $this->State->Value;
			$this->_value->PostalCode = $this->Zip->Value;

			if (is_set($this->Country->Value))
			{
				$this->_value->CountryCode = $this->Country->Value;
			}
			else
			{
				$this->_value->CountryCode = "US";
			}
		}
		else
		{
			$this->_value = null;
		}
	}

	protected function ValidateParts()
	{
		$returnValue = true;

		if (is_set($this->Street->Value) == false)
		{
			$returnValue = false;
		}

		if (is_set($this->City->Value) == false)
		{
			$returnValue = false;
		}

		if (is_set($this->State->Value) == false)
		{
			$returnValue = false;
		}

		if (is_set($this->Zip->Value) == false)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function SetupControls()
	{

		parent::SetupControls();

		$this->Street = new TextBoxControl();
		$this->Street->LabelText = "Street:";	
    $this->Street->BodyStyle->AddClass("address_street");

		$this->City = new TitleTextBoxControl();
		$this->City->LabelText = "City";	
    $this->City->BodyStyle->AddClass("address_city");

		$this->State = new TitleTextBoxControl();
		$this->State->LabelText = "State / Province";	
    $this->State->BodyStyle->AddClass("address_state");

		$this->Zip = new TitleTextBoxControl();
		$this->Zip->LabelText = "Postal Code";	
    $this->Zip->BodyStyle->AddClass("address_zip");

		$this->Country = new DropDownControl();
		$this->Country->LabelText = "Country:";
		$this->Country->AddElement("US","United States", true);
		$this->Country->AddElement("CA","Canada");
    $this->Country->BodyStyle->AddClass("address_country");
	}

	public function Render()
	{
		if ($this->_countryMode == self::US_CANADA)
		{
			$this->Template->IsMultiCountry = true;
		}

		$returnValue = parent::Render();
		
		return $returnValue;
	}

}

