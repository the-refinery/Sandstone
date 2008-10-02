<?php
/*
Credit Card Control Class File

@package Sandstone
@subpackage Application
*/

NameSpace::Using("Sandstone.CreditCard");

class CreditCardControl extends BaseControl
{

	protected $_startYear;
	protected $_endYear;

	public function __construct()
	{
		parent::__construct();

        //Setup the default style classes
		$this->_controlStyle->AddClass('creditcard_general');
		$this->_bodyStyle->AddClass('creditcard_body');

		// Default Start and End Years
		$currentDate = new Date();
		$this->_startYear = $currentDate->Year;
		$this->_endYear = $currentDate->Year + 4;

		//Load our dropdowns
		$this->LoadCardTypeDropDown();
    	$this->LoadMonthDropDown();
		$this->LoadYearDropDown();

		$this->_isRawValuePosted = false;

	}

	protected function LoadCardTypeDropDown()
	{
		$this->CardType->ClearElements();

		$currentLicense =  Application::License();

		//Does the current license support active card types?
		if ($currentLicense->hasProperty("ActiveCreditCardTypes"))
		{
			//Show just the active ones
			foreach ($currentLicense->ActiveCreditCardTypes as $tempType)
			{
				$this->CardType->AddElement($tempType->CardTypeID, $tempType->Name);
			}
		}
		else
		{
			//Show all
			$types = CreditCardType::LookupAll();

			foreach ($types->ItemsByKey as $tempType)
			{
				$this->CardType->AddElement($tempType->CardTypeID, $tempType->Name);
			}
		}

	}

	protected function LoadMonthDropDown()
	{

		$this->Month->ClearElements();

		//Start with a blank one.
		$this->Month->AddElement(0, "");

   		for ($i = 1; $i <= 12; $i++)
		{
			$monthName = Date::MonthName($i);

			$this->Month->AddElement($i, $monthName);
		}
	}

	protected function LoadYearDropdown()
	{

		//Clear any existing elements
		$this->Year->ClearElements();

        //Start with a blank one.
		$this->Year->AddElement(0, "");

		//And loop from start to end years
        for ($i = $this->_startYear; $i <= $this->_endYear; $i++)
		{
			$this->Year->AddElement($i, $i);
		}

	}

	protected function SetupControls()
	{
		parent::SetupControls();

		$this->CardType = new DropDownControl();
		$this->CardType->ControlStyle->AddClass("creditcard_cardtypeitem");
		$this->CardType->LabelText = "Card Type";

		$this->CardNumber = new TextBoxControl();
		$this->CardNumber->ControlStyle->AddClass("creditcard_cardnumberitem");
		$this->CardNumber->LabelText = "Card Number";

		$this->NameOnCard= new TextBoxControl();
		$this->NameOnCard->ControlStyle->AddClass("creditcard_nameoncarditem");
		$this->NameOnCard->LabelText = "Name on Card";

		$this->Month = new DropDownControl();
		$this->Month->ControlStyle->AddClass("creditcard_monthitem");
		$this->Month->LabelText = "Month";

   		$this->Year = new DropDownControl();
		$this->Year->ControlStyle->AddClass("creditcard_yearitem");
		$this->Year->LabelText = "Year";

		$this->CVV= new TextBoxControl();
		$this->CardNumber->ControlStyle->AddClass("creditcard_cvvitem");
		$this->CVV->LabelText = "CVV";

	}

	protected function ParseEventParameters()
	{

		if ($this->CardType->Value > 0)
		{
			$selectedCardType = new CreditCardType($this->CardType->Value);
		}

		//Clean up the card number, removing spaces and dashes
		$enteredNumber = str_replace(Array(" ", "-"), "", $this->CardNumber->Value);
		$enteredNameOnCard = $this->NameOnCard->Value;

		if ($this->Month->Value > 0 && $this->Year->Value > 0)
		{
			$expirationDate = new Date("{$this->Month->Value}/1/{$this->Year->Value}");
		}

		$enteredCVV = $this->CVV->Value;


		//If everything has been entered, create a credit card object.
		if (is_set($selectedCardType) && is_set($enteredNumber) && is_set($enteredNameOnCard) && is_set($expirationDate) && is_set($enteredCVV))
		{
			$this->_value = new CreditCard();
			$this->_value->CardType = $selectedCardType;
			$this->_value->Number = $enteredNumber;
			$this->_value->NameOnCard = $enteredNameOnCard;
			$this->_value->ExpirationDate = $expirationDate;
			$this->_value->CVV = $enteredCVV;
		}

	}

	public function Render()
	{

		if (count($this->CardType->Elements) == 0)
		{
			//change to the "cards not accepted" template
			$this->_template->FileName = "creditcard_notaccepted";
		}

		$returnValue =  parent::Render();

        return $returnValue;
	}

}
?>