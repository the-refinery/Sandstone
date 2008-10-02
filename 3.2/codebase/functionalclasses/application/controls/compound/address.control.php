<?php
/*
Address Control Class File

@package Sandstone
@subpackage Application
*/

Namespace::Using("Sandstone.Utilities.String");

class AddressControl extends BaseControl
{

	protected $_defaultValue;

    public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_bodyStyle->AddClass('address_body');

		$this->_isTopLevelControl = true;
		$this->_isRawValuePosted = false;

        $this->_template->FileName = "address";

    }

	/*
	DefaultValue property

	@return address
	@param address $Value
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
			$this->CityStateZip->DefaultValue = "{$Value->City}, {$Value->ProvinceCode} {$Value->PostalCode}";
			$this->CountryCode->DefaultValue = $Value->CountryCode;

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
			$this->CityStateZip->DefaultValue = null;
			$this->CountryCode->DefaultValue = null;
		}
	}

	protected function ParseEventParameters()
	{

		if (is_set($this->Street->Value) && is_set($this->CityStateZip->Value))
		{
			$this->_value = new Address();
			$this->_value->Street = $this->Street->Value;

			$cityStateZip = $this->ParseCityStateZip($this->CityStateZip->Value);

			if (count($cityStateZip) == 3)
			{
				$this->_value->City = $cityStateZip['city'];
				$this->_value->ProvinceCode = $cityStateZip['state'];
				$this->_value->CountryCode = $this->CountryCode->Value;
			}
			else
			{
				//We need to lookup the zip and take the default values
				$postalCode = new PostalCode($cityStateZip['zip']);

				if ($postalCode->IsLoaded)
				{
					$this->_value->City = $postalCode->CityName;
					$this->_value->ProvinceCode = $postalCode->Province->ProvinceCode;
					$this->_value->CountryCode = $postalCode->Province->Country->CountryCode;
				}
			}

			$this->_value->PostalCode = $cityStateZip['zip'];

			//Default to a US address if nothing entered
			if (is_set($this->_value->CountryCode) == false)
			{
				$this->_value->CountryCode = "US";
			}
		}
		else
		{
			$this->_value = null;
		}
	}

	protected function ParseCityStateZip($InputString)
	{
		$returnValue = new DIarray();

		if (strpos($InputString, ",") === false)
		{
			//No comma, so treat it as a postal code only
			$returnValue['zip'] = trim($InputString);
		}
		else
		{
			$commaPosition = strpos($InputString, ",");
			$lastSpacePosition = strrpos($InputString, " ");

			//Check for Canadian postal codes
			if ($lastSpacePosition == strlen($InputString) - 4)
			{
				$lastSpacePosition = strrpos($InputString, " ", -5);
			}

			$returnValue['city'] = trim(substr($InputString, 0, $commaPosition));
			$returnValue['zip'] = trim(substr($InputString, $lastSpacePosition));

			$state = trim($InputString);
			$state = str_replace($returnValue['city'], "", $state);
			$state = str_replace($returnValue['zip'], "", $state);
			$returnValue['state'] = trim(StringFunc::RemovePunctuation($state));
		}

		return $returnValue;
	}

    protected function SetupControls()
	{

		parent::SetupControls();

		$this->Street = new TextAreaControl();
		$this->Street->ControlStyle->AddClass("address_streetitem");
		$this->Street->Rows = 2;
		$this->Street->Columns = 30;
		$this->Street->LabelText = "Street:";		

   		$this->CityStateZip= new TitleTextBoxControl();
		$this->CityStateZip->ControlStyle->AddClass("address_citystatezipitem");
		$this->CityStateZip->Template->FileName = "addresscitystatezip";
		$this->CityStateZip->LabelText = "City, State  Zip (or Zip Only)";

		$this->PickList = new RepeaterControl();
		$this->PickList->SetCallback($this, "PickListCallBack");
		$this->PickList->Template->FileName = "address_picklist";

		$this->CountryCode = new HiddenControl();

	}

	public function AJAX_Autocomplete($Processor)
	{

		$Processor->Template->ControlName = $this->Name;

		$cityStateZip = $this->ParseCityStateZip($this->CityStateZip->Value);

		if (count($cityStateZip) == 3)
		{
			//All 3 passed
			$this->CityMatchAutocomplete($Processor, $cityStateZip);
		}
		else
		{
			$this->ZipCodeAutocomplete($Processor, $cityStateZip['zip']);
		}
	}

	protected function CityMatchAutocomplete($Processor, $CityStateZip)
	{
		//Does the City and State match anything in the zip?
		$postalCode = new PostalCode($CityStateZip['zip']);

		if ($postalCode->IsLoaded)
		{
			//Loop the cities and see if we get a match
			$isFound = false;

			foreach ($postalCode->Cities as $tempCity)
			{
				if (strtolower($CityStateZip['city']) == strtolower($tempCity->CityName))
				{
					//City matches, how about the state?
					if (strtoupper($CityStateZip['state']) == $postalCode->Province->ProvinceCode)
					{
						$isFound = true;
						$matchCityName = $tempCity->CityName;
					}
				}
			}

			//Did we find a match?
			if ($isFound)
			{
				//Update the textbox to whatever is in the DB
				$Processor->Template->FileName = "address_autocomplete_city_match";
				$Processor->Template->CityStateZip = "{$matchCityName}, {$postalCode->Province->ProvinceCode} {$postalCode->PostalCode}";
				$Processor->Template->CountryCode = $postalCode->Province->Country->CountryCode;
			}
			else
			{
				//Show the pick list
				$this->MultiMatchAutocompleteResults($Processor, $postalCode, true);
			}
		}
		else
		{
			if ($postalCode->IsInvalidFormat)
			{
				//Zip Code Not Found
				$Processor->Template->FileName = "address_autocomplete_zip_invalidformat";
			}
			else
			{
				//Unknown postal code, so just accept what is there
				$Processor->Template->FileName = "address_autocomplete_city_ok";
			}
		}


	}

	protected function ZipCodeAutocomplete($Processor, $ZipCode)
	{
		//Check just the zip
		$postalCode = new PostalCode($ZipCode);

		if ($postalCode->IsLoaded)
		{
			//Is there more than one city?
			if (count($postalCode->Cities) > 1)
			{
				//Some to pick from
				$this->MultiMatchAutocompleteResults($Processor, $postalCode);
			}
			else
			{
				//Just one
				$Processor->Template->FileName = "address_autocomplete_zip_onematch";
				$Processor->Template->CityStateZip = "{$postalCode->CityName}, {$postalCode->Province->ProvinceCode} {$postalCode->PostalCode}";
				$Processor->Template->CountryCode = $postalCode->Province->Country->CountryCode;
			}
		}
		else
		{
			if ($postalCode->IsInvalidFormat)
			{
				//Zip Code Not Found
				$Processor->Template->FileName = "address_autocomplete_zip_invalidformat";
			}
			else
			{
				//Zip Code Not Found
				$Processor->Template->FileName = "address_autocomplete_zip_nomatch";
			}
		}

	}

	protected function MultiMatchAutocompleteResults($Processor, $PostalCode, $IsMineIncluded = false)
	{
		$Processor->Template->FileName = "address_autocomplete_multimatch";
		$Processor->Template->PickListHTML = $this->BuildPickListHTML($PostalCode, $IsMineIncluded);
		$Processor->Template->PickListObservers = $this->BuildPickListObservers($PostalCode, $IsMineIncluded);
	}

	protected function BuildPickListHTML($PostalCode, $IsMineIncluded = false)
	{

		$this->PickList->Template->RequestFileType = "htm";
		$this->PickList->Template->IsMineIncluded = $IsMineIncluded;
		$this->PickList->Data = $PostalCode->Cities;

		$returnValue = $this->PickList->Render();

		//Compress the output so we can use it via AJAX
		$returnValue = $this->CompressHTMLoutput($returnValue);

		return $returnValue;
	}

	protected function BuildPickListObservers($PostalCode, $IsMineIncluded = false)
	{

		if ($IsMineIncluded)
		{
			$returnValue = "Event.observe('{$this->PickList->Name}_KeepMine', 'click', {$this->Name}_ClosePickList);\n";
		}

		for($i = 1; $i <= count($PostalCode->Cities); $i++)
		{
			$returnValue .= "Event.observe('{$this->PickList->Name}_Item_{$i}', 'click', {$this->PickList->Name}_ChooseCity);\n";
		}

		return $returnValue;
	}

	public function PickListCallBack($CurrentElement, $Template)
	{
		//Set its template
		$Template->FileName = "address_picklist_item";
	}

	public function Render()
	{
		$this->CityStateZip->Template->ParentControlName = $this->Name;
		$this->PickList->Template->ParentControlName = $this->Name;

		$returnValue = parent::Render();

		return $returnValue;

	}

}
?>
