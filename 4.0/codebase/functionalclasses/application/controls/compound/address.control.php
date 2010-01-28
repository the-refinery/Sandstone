<?php
/*
Address Control Class File

@package Sandstone
@subpackage Application
*/

Namespace::Using("Sandstone.Utilities.String");
Namespace::Using("Sandstone.Address");

class AddressControl extends BaseControl
{
	protected $_defaultValue;

	public function __construct()
	{
		parent::__construct();

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;

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
			$this->_value->CountryCode = "US";
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

		$this->City = new TextBoxControl();
		$this->City->LabelText = "City:";	

		$this->State = new TextBoxControl();
		$this->State->LabelText = "State / Province:";	

		$this->Zip = new TextBoxControl();
		$this->Zip->LabelText = "Zip Code / Postal Code:";	
	}

}
