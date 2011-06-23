<?php
/**
Credit Card Class

@package Sandstone
@subpackage CreditCard
*/

SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.Date");
SandstoneNamespace::Using("Sandstone.Merchant");

class CreditCard extends Module
{

	protected $_partA;
	protected $_partB;
	protected $_partC;
	protected $_cardType;
	protected $_cvv;
	protected $_nameOnCard;
	protected $_billingAddress;
	protected $_expirationDate;

	protected $_latestTransaction;

	protected $_acceptedCardTypes;

	protected $_invalidProperty;

	public function __construct()
	{

		$this->_acceptedCardTypes = new DIarray();
	}

	/**
	Number property

	@return int
	@param int $Value
	*/
	public function getNumber()
	{
		if (is_set($this->_partA))
		{
			$returnValue = $this->_partA . $this->_partB . $this->_partC;
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	public function setNumber($Value)
	{
		if (is_set($Value))
		{
			//Make sure it is numeric value only
			$Value = ereg_replace('[^0-9]', '', $Value);

			if (strlen($Value) >= 13 && strlen($Value) <= 16)
			{
				$this->_partA = substr($Value, 0, 4);
				$this->_partB = substr($Value, 4, strlen($Value) - 8);
				$this->_partC = substr($Value, -4);
			}
			else
			{
				$this->_partA = null;
				$this->_partB = null;
				$this->_partC = null;
			}

		}

	}

	public function getPartC()
	{
		return $this->_partC;
	}

	/**
	CardType property

	@return string
	@param string $Value
	*/
	public function getCardType()
	{
		return $this->_cardType;
	}

	public function setCardType($Value)
	{
		if ($Value instanceof CreditCardType && $Value->IsLoaded && $Value->IsAccepted)
		{
			$this->_cardType = $Value;
		}
		else
		{
			$this->_cardType = null;
		}
	}

	/**
	CVV property

	@return
	@param  $Value
	*/
	public function getCVV()
	{
		return $this->_cvv;
	}

	public function setCVV($Value)
	{
		if (is_set($Value) && is_numeric($Value))
		{
			$this->_cvv = $Value;
		}
		else
		{
			$this->_cvv = null;
		}

	}

	/**
	NameOnCard property

	@return string
	@param string $Value
	*/
	public function getNameOnCard()
	{
		return $this->_nameOnCard;
	}

	public function setNameOnCard($Value)
	{
		if (is_set($Value))
		{
			$this->_nameOnCard = $Value;
		}
		else
		{
			$this->_nameOnCard = null;
		}
	}

	public function getFirstName()
	{
		$tempNameArray = explode(" ", $this->_nameOnCard);

		return $tempNameArray[0];
	}

	public function getLastName()
	{
		$tempNameArray = explode(" ", $this->_nameOnCard);

		return $tempNameArray[1];
	}


	/**
	BillingAddress property

	@return string
	@param string $Value
	*/
	public function getBillingAddress()
	{
		return $this->_billingAddress;
	}

	public function setBillingAddress($Value)
	{
		if ($Value instanceof Address)
		{
			$this->_billingAddress = $Value;
		}
		else
		{
			$this->_billingAddress = null;
		}
	}

	/**
	ExpirationDate property

	@return date
	@param date $Value
	*/
	public function getExpirationDate()
	{
		return $this->_expirationDate;
	}

	public function setExpirationDate($Value)
	{
		if ($Value instanceof Date)
		{
			$this->_expirationDate = $Value;
		}
		else
		{
			$this->_expirationDate = null;
		}
	}

	public function getLatestTransaction()
	{
		return $this->_latestTransaction;
	}

	/**
	AcceptedCardTypes property

	@return
	*/
	public function getAcceptedCardTypes()
	{
		if (is_set($this->_acceptedCardTypes) == false)
		{
			$this->LoadAcceptedCardTypes();
		}

		return $this->_acceptedCardTypes;
	}

	/*
	IsValid property

	@return boolean
	*/
	public function getIsValid()
	{
		$returnValue = is_set($this->_billingAddress);

    if ($returnValue == false)
    {
      $this->_invalidProperty = "Billing Address";
    }

		if ($returnValue == true)
		{
			$returnValue = $this->ValidateCardNumber();
		}

		if ($returnValue == true)
		{
			$returnValue = $this->ValidateCVV();
		}

		if ($returnValue == true)
		{
			$returnValue = $this->ValidateExpirationDate();
		}

		if ($returnValue == true)
		{
			$returnValue = $this->ValidateNameOnCard();
		}

		return $returnValue;
	}

	public function getInValidProperty()
	{
		return $this->_invalidProperty;
	}

	public function getIsLoaded()
	{
		$returnValue = false;

		if(is_set($this->Number))
		{
			$returnValue = true;
		}

		return $returnValue;
	}

	public function LoadAcceptedCardTypes()
	{

		$query = new Query();

		$query->SQL = "	SELECT 	CardTypeID,
														Name,
														IsAccepted
										FROM		core_CreditCardTypeMaster
										WHERE 	IsAccepted = 1";

		$query->Execute();

		$query->LoadEntityArray($this->_acceptedCardTypes, "CreditCardType", "CardTypeID");

		return true;

	}

	protected function ValidateCardNumber()
	{

		//Build the full number, the check digit, and the total length
		$fullNumber = $this->_partA . $this->_partB . $this->_partC;
		$checkDigit = substr($fullNumber, -1);
		$totalLength = strlen($fullNumber);

		//Validate that the number has the correct PartA and Length for
		//the selected CardType
		if (is_set($this->_cardType))
		{
			$returnValue = $this->_cardType->ValidateCardNumber($this->_partA, $totalLength);
		}
		else
		{
			$returnValue = false;
		}

		if ($returnValue == true && $fullNumber <> "4222222222222222")
		{

			//Calculate the check digit, and compare it.

			/*
				To calculate the check digit:

			   1. First drop the last digit from the card number (because that’s what we are trying to calculate)
			   2. Reverse the number
			   3. Multiply all the digits in odd positions (The first digit, the third digit, etc) by 2.
			   4. If any one is greater than 9 subtract 9 from it.
			   5. Sum those numbers up
			   6. Add the even numbered digits (the second, fourth, etc) to the number you got in the previous step
			   7. The check digit is the amount you need to add to that number to make a multiple of 10. So if you got 68 in the previous step the check digit would be 2.

			*/

			$checksum = 0;

			//Reverse the string
			$reversedNumber= strrev(substr($fullNumber,0, $totalLength - 1));

			//Build the checksum
			for ($i = 1; $i <= strlen($reversedNumber); $i++)
			{
				$pos = $i - 1;
				$char = substr($reversedNumber, $pos, 1);

				if ($i % 2 == 1)
				{
					$tempValue = $char * 2;

					if ($tempValue >= 10)
					{
						$tempValue -= 9;
					}

					$checksum += $tempValue;

				}
				else
				{
					$checksum += $char;
				}
			}


			//Calculate the check digit
			$calculatedCheckdigit = 10 - ($checksum % 10);

			if ($calculatedCheckdigit == 10)
			{
				$calculatedCheckdigit = 0;
			}

			if ($checkDigit == $calculatedCheckdigit)
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}

		}

		if ($returnValue == false)
		{
			$this->_invalidProperty = "Card Number";
		}

		return $returnValue;
	}

	protected function ValidateCVV()
	{
		$returnValue = false;

		if (is_set($this->_cvv))
		{
			if ($this->_cardType->CardTypeID == 2)
			{
				if (strlen($this->_cvv) == 4)
				{
					$returnValue = true;
				}
			}
			else
			{
				if (strlen($this->_cvv) == 3)
				{
					$returnValue = true;
				}
			}
		}


		if ($returnValue == false)
		{
			$this->_invalidProperty = "CVV";
		}

		return $returnValue;
	}

	protected function ValidateExpirationDate()
	{

		$secondsIn4years = 31556926 * 4;

		if (is_set($this->_expirationDate))
		{
			if ($this->_expirationDate->UnixTimestamp > time() && $this->_expirationDate->UnixTimestamp < (time() + $secondsIn4years))
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}

		if ($returnValue == false)
		{
			$this->_invalidProperty = "Expiration Date";
		}

		return $returnValue;

	}

	protected function ValidateNameOnCard()
	{

		if (is_set($this->_nameOnCard))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
			$this->_invalidProperty = "Name On Card";
		}

		return $returnValue;

	}

	public function ProcessAuthorization($Amount)
	{
		//Aquire the Authorization
		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$newTransaction = $merchantAccount->ProcessAuthorization($this, $Amount);

		$returnValue = $this->HandleProcessedTransaction($newTransaction);

		return $returnValue;

	}

	public function ProcessAuthorizationAndCapture($Amount )
	{

		//Process the Sale
		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$newTransaction = $merchantAccount->ProcessAuthorizationAndCapture($this, $Amount);

		$returnValue = $this->HandleProcessedTransaction($newTransaction);

		return $returnValue;

	}

	protected function HandleProcessedTransaction($NewTransaction)
	{

		if (is_set($NewTransaction))
		{
			//Save the transaction
			$this->_latestTransaction = $NewTransaction;

			//Determine return value
			$returnValue = $NewTransaction->IsSuccessful;
		}
		else
		{
			$returnValue = false;
			$this->_latestTransaction = null;
		}

		return $returnValue;
	}

	public function SetupMerchantProcessor($MerchantProcessor)
	{

		if ($MerchantProcessor instanceof ProcessorBase)
		{
			//Get the PartB of the card
			$returnValue = $this->LoadPartB();

			if ($returnValue == true)
			{
				//Get First and Last Names
				$tempNameArray = explode(" ", $this->_nameOnCard);


				$MerchantProcessor->CreditCardID = $this->_creditCardID;
				$MerchantProcessor->CardType = $this->_cardType->Name;
				$MerchantProcessor->Number = $this->_partA . $this->_partB . $this->_partC;
				$MerchantProcessor->ExpirationDate = $this->_expirationDate;
				$MerchantProcessor->CVV = $this->_cvv;

				$MerchantProcessor->FirstName = $tempNameArray[0];
				$MerchantProcessor->LastName = $tempNameArray[1];
				$MerchantProcessor->BillingAddress = $this->_billingAddress;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	static public function TestCard()
	{
		$returnValue = new CreditCard;

		$returnValue->Number = "4719225120691156";
		$returnValue->CardType = new CreditCardType(5);
		$returnValue->CVV = "456";

		$now = new Date();
		$returnValue->ExpirationDate = $now->AddYears(2);

		$returnValue->NameOnCard = "Test User";

		$addr = new Address();
		$addr->Street = "20545 Center Ridge Road";
		$addr->City = "Rocky River";
		$addr->ProvinceCode = "OH";
		$addr->PostalCode = "44116";
		$addr->CountryCode = "US";

		$returnValue->BillingAddress = $addr;

		return $returnValue;
	}

}
