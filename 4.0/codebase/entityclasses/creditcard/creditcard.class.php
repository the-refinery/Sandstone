<?php
/**
Credit Card Class

@package Sandstone
@subpackage CreditCard
*/

NameSpace::Using("Sandstone.Database");
NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.Date");
NameSpace::Using("Sandstone.Merchant");

class CreditCard extends Module
{

	protected $_creditCardID;
	protected $_partA;
	protected $_partB;
	protected $_partC;
	protected $_cardType;
	protected $_cvv;
	protected $_nameOnCard;
	protected $_billingAddress;
	protected $_expirationDate;

	protected $_transactions;
	protected $_latestTransaction;

	protected $_acceptedCardTypes;

	public function __construct($ID = null)
	{

		$this->_transactions = new DIarray();
		$this->_acceptedCardTypes = new DIarray();

		if (is_set($ID))
		{
			if (is_array($ID))
			{
				$this->Load($ID);
			}
			else
			{
				$this->LoadByID($ID);
			}
		}
	}

	/**
	CreditCardID property

	@return int
	*/
	public function getCreditCardID()
	{
		return $this->_creditCardID;
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
			$returnValue = $this->_partA;
			$returnValue = str_pad($returnValue, strlen($this->_partB) + 4,  "x");
			$returnValue .= $this->_partC;
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

	/**
	PartB property

	@return string
	*/
	public function getPartB()
	{
		if (is_set($this->_partB) && substr($this->_partB, 0, 1) != 'x')
		{
			$returnValue = $this->_partB;
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
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
		if (is_set($Value) && is_numeric($Value) && strlen($Value) == 3)
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
			$this->_nameOnCard = substr(trim($Value), 0, DB_NAME_MAX_LEN);
		}
		else
		{
			$this->_nameOnCard = null;
		}
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
		if ($Value instanceof Address && $Value->IsLoaded)
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

	/**
	Transactions property

	@return array
	*/
	public function getTransactions()
	{
		if (is_set($this->_transactions) == false)
		{
			$this->LoadTransactions();
		}

		return $this->_transactions;
	}

	/**
	LatestTransaction property

	@return transaction
	*/
	public function getLatestTransaction()
	{
		if (is_set($this->_latestTransaction) == false)
		{
			$this->LoadTransactions();
		}

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

	public function Load($dr)
	{
		$this->_creditCardID = $dr['CreditCardID'];

		$this->_partA = $dr['PartA'];
		$this->_partB = str_pad($this->_partB, $dr['NumberLength'] - 8,  "x");
		$this->_partC = $dr['PartC'];

		$this->_cardType = new CreditCardType($dr['CardTypeID']);

		$this->_cvv = $dr['CVV'];

		$this->_nameOnCard = $dr['NameOnCard'];

		if (is_set($dr['AddressID']))
		{
			$this->_billingAddress = new Address($dr['AddressID'], $conn);
		}

		$this->_expirationDate = new date($dr['ExpirationDate']);

		$this->_isLoaded = true;

		return true;
	}

	public function LoadByID($ID)
	{

		$query = new Query();

		$query->SQL = "	SELECT 	CreditCardID,
								PartA,
								PartC,
								NumberLength,
								CardTypeID,
								CVV,
								NameOnCard,
								AddressID,
								ExpirationDate
						FROM 	core_CreditCardMaster
						WHERE 	CreditCardID = {$ID}";

		$query->Execute();

		$returnValue = $query->LoadEntity($this);

		return $returnValue;

	}

	public function LoadTransactions()
	{

		$query = new Query();

		$query->SQL = "	SELECT	TransactionID,
								CreditCardID,
								Timestamp,
								Amount,
								MerchantTransactionID,
								IsSuccessful
						FROM	core_CreditCardTransactionMaster
						WHERE	CreditCardID = {$this->_creditCardID}
						ORDER BY Timestamp";

		$query->Execute();

		$query->LoadEntityArray($this->_transactions, "CreditCardTransaction", "TransactionID", $this, "SetupLatestTransaction");

		return true;
	}

	public function SetupLatestTransaction($Transaction)
	{
		$this->_latestTransaction = $Transaction;
	}

	public function LoadAcceptedCardTypes()
	{

		$query = new Query();

		$query->SQL = "	SELECT 	CardTypeID,
								Name,
								IsAccepted
						FROM 	core_CreditCardTypeMaster
						WHERE 	IsAccepted = 1";

		$query->Execute();

		$query->LoadEntityArray($this->_acceptedCardTypes, "CreditCardType", "CardTypeID");

		return true;

	}

	protected function LoadPartB()
	{
		if (is_numeric($this->_partB) == false)
		{
			$query = new Query();

			$query->SQL = "	SELECT 	SecurityCode
							FROM	core_TransactionCodeMaster
							WHERE	TransactionID = {$this->_creditCardID}";

			$query->Execute();

			if ($query->SelectedRows > 0)
			{
				$this->_partB = $this->DecryptPartB($query->SingleRowResult['SecurityCode']);
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = true;
		}

		return $returnValue;

	}

	public function Save()
	{
		//Only save a new record if this is valid card info.
		//We do not save any updates.
		if ($this->getIsValid())
		{
			$this->SaveNewRecord();
			$this->SavePartB();
			$this->_isLoaded = true;
		}

	}

	protected function SaveNewRecord()
	{

		$query = new Query();

		$tempNumberLength = strlen($this->_partA . $this->_partB . $this->_partC);

		$accountID = Application::License()->AccountID;

		$query->SQL = "	INSERT INTO core_CreditCardMaster
						(
							AccountID,
							PartA,
							PartC,
							NumberLength,
							CardTypeID,
							CVV,
							NameOnCard,
							AddressID,
							ExpirationDate
						)
						VALUES
						(
							{$accountID},
							{$query->SetTextField($this->_partA)},
							{$query->SetTextField($this->_partC)},
							{$tempNumberLength},
							{$this->_cardType->CardTypeID},
							'{$this->_cvv}',
							{$query->SetTextField($this->_nameOnCard)},
							{$this->_billingAddress->AddressID},
							'{$this->_expirationDate->MySQLtimestamp}'
						)";

		$query->Execute();

		//Get the new ID
		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$this->_creditCardID = $query->SingleRowResult['newID'];

	}

	protected function SavePartB()
	{
		if (is_numeric($this->_partB))
		{
			$query = new Query();

			//Clear any existing records
			$query->SQL = "	DELETE
							FROM	core_TransactionCodeMaster
							WHERE	TransactionID = {$this->_creditCardID}";

			$query->Execute();

			//Encrypt the PartB
			$encryptedPartB = $this->EncryptPartB($this->_partB);

			//Insert a new record
			$query->SQL = "	INSERT INTO core_TransactionCodeMaster
							(
								TransactionID,
								SecurityCode
							)
							VALUES
							(
								{$this->_creditCardID},
								'{$encryptedPartB}'
							)";

			$query->Execute();
		}
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

		return $returnValue;
	}

	protected function ValidateCVV()
	{
		if (is_set($this->_cvv))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
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
		}

		return $returnValue;

	}

	protected function EncryptPartB($PartB)
	{
		//Split the string into an array
		$encryptArray = str_split($PartB);

		//Loop through each character in the string.  Encode it
		//then pair it with a random character.  This is level 1 encryption.
		for ($i = 0; $i < count($encryptArray); $i++)
		{
			//Actual Data

			//Choose Number, uppper or lowercase
			$randType = mt_rand(1, 3);

			switch($randType)
			{
				case 1:
					//Number
					$encodeA = $encryptArray[$i];
					break;

				case 2:
					//Lowercase
					$encodeA = chr(97 + $i + $encryptArray[$i]);
					break;

				case 3:
					//Uppercase
					$encodeA = chr(65 + $i + $encryptArray[$i]);
					break;
			}

			//Random character

			//Choose Number, uppper or lowercase
			$randType = mt_rand(1, 3);

			switch($randType)
			{
				case 1:
					//Number
					$encodeB = mt_rand(0, 9);
					break;

				case 2:
					//Lowercase
					$encodeB = chr(mt_rand(0,25) + 97);
					break;

				case 3:
					//Uppercase
					$encodeB = chr(mt_rand(0,25) + 65);
					break;
			}

		   //Build the output string
		   $levelOneEncrypt .= $encodeA . $encodeB;
		}

		//Perform the Level 2 encryption
		$returnValue = DIencrypt::Encrypt($levelOneEncrypt);

		return $returnValue;
	}

	protected function DecryptPartB($PartB)
	{

		//Decrypt the Level 2 Encode
		$levelOneEncrypt = DIencrypt::Decrypt($PartB);

		//Split resulting string into an array
		$decryptArray = str_split($levelOneEncrypt);

		//Decypt the Level 1 Encode
		for ($i = 0; $i < count($decryptArray); $i++)
		{
			if ($i % 2 == 0)
			{
				if (is_numeric($decryptArray[$i]))
				{
					$returnValue .= $decryptArray[$i];
				}
				else
				{
					$returnValue .= (ord(strtoupper($decryptArray[$i])) - 65)	- ($i / 2);
				}

			}
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

	public function ProcessCharge($Amount, $AuthTransaction = null)
	{

		//Process the Sale
		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$newTransaction = $merchantAccount->ProcessCharge($this, $Amount, $AuthTransaction);

		$returnValue = $this->HandleProcessedTransaction($newTransaction);

		return $returnValue;

	}

	public function ProcessCredit($Amount, $ChargeTransaction = null)
	{
		//Process the Sale

		$merchantAccount = Application::License()->ActiveMerchantAccount;
		$newTransaction = $merchantAccount->ProcessCredit($this, $Amount, $ChargeTransaction);

		$returnValue = $this->HandleProcessedTransaction($newTransaction);

		return $returnValue;

	}

	protected function HandleProcessedTransaction($NewTransaction)
	{

		if (is_set($NewTransaction))
		{
			//Save the transaction
			$this->_transactions[$NewTransaction->TransactionID] = $NewTransaction;
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

}
?>