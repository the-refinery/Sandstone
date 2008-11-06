<?php
/*
MerchantAccountProcessor Class

@package Sandstone
@subpackage Merchant
*/

NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.CreditCard");

class ProcessorBase extends Module
{
	protected $_parameters;

	protected $_creditCardID;

	protected $_cardType;
	protected $_number;
	protected $_expirationDate;
	protected $_cvv;

	protected $_firstName;
	protected $_lastName;
	protected $_billingAddress;

	protected $_amount;

	public function __construct($Parameters)
	{
		$this->_parameters = $Parameters;
	}

	/*
	CreditCardID property

	@return integer
	@param integer $Value
	*/
	public function getCreditCardID()
	{
		return $this->_creditCardID;
	}

	public function setCreditCardID($Value)
	{
		$this->_creditCardID = $Value;
	}

	/*
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
		$this->_cardType = $Value;
	}

	/*
	Number property

	@return string
	@param string $Value
	*/
	public function getNumber()
	{
		return $this->_number;
	}

	public function setNumber($Value)
	{
		$this->_number = $Value;
	}

	/*
	ExpirationDate property

	@return Date
	@param Date $Value
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
	}

	/*
	Cvv property

	@return string
	@param string $Value
	*/
	public function getCVV()
	{
		return $this->_cvv;
	}

	public function setCVV($Value)
	{
		$this->_cvv = $Value;
	}

	/*
	FirstName property

	@return string
	@param string $Value
	*/
	public function getFirstName()
	{
		return $this->_firstName;
	}

	public function setFirstName($Value)
	{
		$this->_firstName = $Value;
	}

	/*
	LastName property

	@return string
	@param string $Value
	*/
	public function getLastName()
	{
		return $this->_lastName;
	}

	public function setLastName($Value)
	{
		$this->_lastName = $Value;
	}

	/*
	BillingAddress property

	@return Address
	@param Address $Value
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

    public function ProcessAuthorization($Amount)
    {
        return null;
    }

	public function ProcessCharge($Amount, $AuthTransaction = null)
	{
		return null;
	}

    public function ProcessCredit($Amount, $ChargeTransaction = null)
    {
        return null;
    }

}