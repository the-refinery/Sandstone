<?php
/**
 * Credit Card Transaction Class
 * 
 * @package Sandstone
 * @subpackage CreditCard
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.ADOdb");
SandstoneNamespace::Using("Sandstone.Address");
SandstoneNamespace::Using("Sandstone.Date");

class CreditCardTransaction extends Module
{
	
	protected $_transactionID;
	protected $_creditCardID;
	protected $_timestamp;
	protected $_amount;
	protected $_merchantTransactionID;
	protected $_isSuccessful;
	
	protected $_messages;

	public function __construct($ID = null)
	{
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
	 * TransactionID property
	 * 
	 * @return int
	 */
	public function getTransactionID()
	{
		return $this->_transactionID;
	}

	/**
	 * CreditCardID property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getCreditCardID()
	{
		return $this->_creditCardID;
	}

	public function setCreditCardID($Value)
	{
		$this->_creditCardID = $Value;
	}

	/**
	 * Timestamp property
	 * 
	 * @return date
	 * 
	 * @param date $Value
	 */
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	public function setTimestamp($Value)
	{
		$this->_timestamp = $Value;
	}

	/**
	 * Amount property
	 * 
	 * @return decimal
	 * 
	 * @param decimal $Value
	 */
	public function getAmount()
	{
		return $this->_amount;
	}

	public function setAmount($Value)
	{
		$this->_amount = $Value;
	}

	/**
	 * MerchantTransactionID property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getMerchantTransactionID()
	{
		return $this->_merchantTransactionID;
	}

	public function setMerchantTransactionID($Value)
	{
		$this->_merchantTransactionID = $Value;
	}
	
	/**
	 * IsSuccessful property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsSuccessful()
	{
		return $this->_isSuccessful;
	}

	public function setIsSuccessful($Value)
	{
		$this->_isSuccessful = $Value;
	}
	
	/**
	 * Messages property
	 * 
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages;
	}	
	
	public function Load($dr)
	{
		
		$this->_transactionID = $dr['TransactionID'];
		$this->_creditCardID = $dr['CreditCardID'];
		$this->_timestamp = new date($dr['Timestamp']);
		$this->_amount = $dr['Amount'];
		$this->_merchantTransactionID = $dr['MerchantTransactionID'];
		$this->_isSuccessful = Connection::GetBooleanField($dr['IsSuccessful']);
		
		$returnValue = $this->LoadMessages();
		
		$this->_isLoaded = $returnValue;
		
		return $returnValue;
		
	}
	
	public function LoadByID($ID)
	{
		
		$conn = GetConnection();
		
		$query = "	SELECT	TransactionID,
							CreditCardID,
							Timestamp,
							Amount,
							MerchantTransactionID,
							IsSuccessful
					FROM	core_CreditCardTransactionMaster
					WHERE	TransactionID = {$ID}";
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();
			$returnValue = $this->Load($dr);
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	public function LoadMessages()
	{
		
		$conn = GetConnection();
		
		$query = "	SELECT 	MessageText
					FROM	core_CreditCardTransactionMessage
					WHERE	TransactionID = {$this->_transactionID}
					ORDER BY MessageID";
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow()) 
			{
				$this->_messages[] = $dr['MessageText'];
			}
		}
		else
		{
			$returnValue = false;
		}

		
		return $returnValue;
	}
	
	public function Save()
	{
		$conn = GetConnection();
		
		//We only create new records,
		//so only save if we don't have a transactionID
		if (is_set($this->_transactionID) == false ||$this->_transactionID == 0)
		{
			$this->SaveNewRecord($conn);
			
			if (count($this->_messages) > 0)
			{
				$this->SaveMessages($conn);	
			}
			
			$this->_isLoaded = true;
		}
			
	}
		
	protected function SaveNewRecord($conn)
	{
		
		$query = "	INSERT INTO core_CreditCardTransactionMaster
					(
						CreditCardID,
						Timestamp,
						Amount,
						MerchantTransactionID,
						IsSuccessful
					)
					VALUES
					(
						{$this->_creditCardID},
						{$conn->SetDateField($this->_timestamp)},
						{$this->_amount},
						{$conn->SetNullTextField($this->_merchantTransactionID)},
						{$conn->SetBooleanField($this->_isSuccessful)}
					)";

		$conn->Execute($query);
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_transactionID = $dr['newID'];
		
	}
	
	protected function SaveMessages($conn)
	{
		
		foreach ($this->_messages as $tempMessage)
		{
			
			$query = "	INSERT INTO core_CreditCardTransactionMessage
						(
							TransactionID,
							MessageText
						)
						VALUES
						(
							{$this->_transactionID},
							'{$tempMessage}'
						)";
	
			$conn->Execute($query);		
		}
		
	}
	
	public function AddMessage($NewMessage)
	{
		$this->_messages[] = $NewMessage;
	}
}

?>