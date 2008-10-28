<?php
/**
 * MerchantAccount Class
 * 
 * @package Sandstone
 * @subpackage Merchant
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.CreditCard");

class MerchantAccount extends Module
{

	protected $_merchantAccountID;
	protected $_name;
	protected $_processorClassName;
	protected $_isProcessAllowed;
	protected $_transactionFee;
	protected $_discountPercent;

	protected $_parameters;
	
	public function __construct($ID=null, $conn=null)
	{

		if (is_set($ID))
		{
			$this->LoadByID($ID, $conn);
		}
		else
		{
			//automatically load the default
			$registry = Application::Registry();

			$this->LoadByID($registry->DefaultMerchantAccountID, $conn);
		}		
	}

	/**
	 * MerchantAccountID property
	 * 
	 * @return integer
	 */
	public function getMerchantAccountID()
	{
		return $this->_merchantAccountID;
	}

	/**
	 * Name property
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * ProcessorClassName property
	 * 
	 * @return string
	 */
	public function getProcessorClassName()
	{
		return $this->_processorClassName;
	}

	/**
	 * IsProcessAllowed property
	 * 
	 * @return boolean
	 */
	public function getIsProcessAllowed()
	{
		return $this->_isProcessAllowed;
	}

	/**
	 * TransactionFee property
	 * 
	 * @return double
	 */
	public function getTransactionFee()
	{
		return $this->_transactionFee;
	}

	/**
	 * DiscountPercent property
	 * 
	 * @return double
	 */
	public function getDiscountPercent()
	{
		return $this->_discountPercent;
	}

	/**
	 * Parameters property
	 * 
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}
		
	public function Load($dr, $conn=null)
	{
		
		$this->_merchantAccountID = $dr['MerchantAccountID'];
		$this->_name = $dr['Name'];
		$this->_processorClassName = $dr['ProcessorClassName'];
		$this->_transactionFee = $dr['TransactionFee'];
		$this->_discountPercent = $dr['DiscountPercent'];
		$this->_isProcessAllowed = Connection::GetBooleanField($dr['IsProcessAllowed']);
		
		$returnValue = $this->LoadParameters($conn);
		
		$this->_isLoaded = $returnValue;

		return $returnValue;

	}
	
	public function LoadByID($ID, $conn)
	{
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();
		}
		
		$query = "	SELECT 	MerchantAccountID,
							Name,
							ProcessorClassName,
							TransactionFee,
							DiscountPercent,
							IsProcessAllowed
					FROM 	core_MerchantAccountMaster
					WHERE 	MerchantAccountID = $ID";
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();
			$returnValue = $this->Load($dr, $conn);
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}
	
	protected function LoadParameters($conn = null)
	{
		if (is_set($conn) == false)
		{
			$conn = GetConnection();
		}
		
		$query = "	SELECT	ParameterName,
							ParameterValue,
							IsEncrypted
					FROM	core_MerchantAccountParameters
					WHERE	MerchantAccountID = {$this->_merchantAccountID}";
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempKey = $dr['ParameterName'];
				$tempValue = $dr['ParameterValue'];
				$isEncrypted = Connection::GetBooleanField($dr['IsEncrypted']);
				
				if ($isEncrypted)
				{
					$tempValue = DIencrypt::Decrypt($tempValue);
				}

				$this->_parameters[$tempKey] = $tempValue;
			}
			
			$returnValue = true;
			
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}
	
	public function ProcessSale($CreditCard, $Amount)
	{

		if ($CreditCard instanceof CreditCard && $CreditCard->IsLoaded && $Amount > 0 && $this->_isProcessAllowed)
		{
			//Creates an object of the class specified from the database.
			$processor = new $this->_processorClassName ($this->_parameters);

			$success = $CreditCard->SetupMerchantProcessor($processor);

			if ($success)
			{
				$returnValue = $processor->ProcessSale($Amount);

				if (is_set($returnValue))
				{
					if ($returnValue->IsSuccessful)
					{
						Action::Log("CreditCardProcessSuccessful", "Credit Card Transaction Processing Successful", $CreditCard->CreditCardID);
					}
					else
					{
						Action::Log("CreditCardProcessFailed", "Credit Card Transaction Processing Failed", $CreditCard->CreditCardID);
					}
				}
				else
				{
					Action::Log("CreditCardProcessFailed", "Credit Card Transaction Processing Failed", $CreditCard->CreditCardID);
				}
			}
			else
			{
				$returnValue = null;
			}

		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}
	
	
}

?>