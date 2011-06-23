<?php
/**
 * Credit Card Type Class
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

class CreditCardType extends Module
{
	protected $_cardTypeID;
	protected $_name;
	protected $_isAccepted;
	
	public function __construct($ID = null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
				$this->Load($ID);
			else
				$this->LoadByID($ID);
		}
	}
	
	/**
	 * CardTypeID property
	 * 
	 * @return int
	 */
	public function getCardTypeID()
	{
		return $this->_cardTypeID;
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
	 * IsAccepted property
	 * 
	 * @return boolean
	 */
	public function getIsAccepted()
	{
		return $this->_isAccepted;
	}
	
	public function Load($dr)
	{
		
		$this->_cardTypeID = $dr['CardTypeID'];
		$this->_name = $dr['Name'];
		$this->_isAccepted = Connection::GetBooleanField($dr['IsAccepted']);
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	CardTypeID,
							Name,
							IsAccepted
					FROM 	core_CreditCardTypeMaster
					WHERE 	CardTypeID = $ID";
		
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

	public function ValidateCardNumber($PartA, $NumberLength)
	{
		
		if ($this->_isLoaded)
		{
			
			switch ($this->_cardTypeID)
			{
				case 1:
					//Diners Club
					if (($PartA >= 3000 && $PartA <= 3059) ||
						($PartA >= 3600 && $PartA <= 3699) ||
						($PartA >= 3800 && $PartA <= 3889))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 14)
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
					break;

				case 2:
					//American Express
					if (($PartA >= 3400 && $PartA <= 3499) ||
						($PartA >= 3700 && $PartA <= 3799))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 15)
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
					break;

				case 3:
					//JCB
					if (($PartA >= 3088 && $PartA <= 3094) ||
						($PartA >= 3096 && $PartA <= 3102) ||
						($PartA >= 3112 && $PartA <= 3120) ||
						($PartA >= 3158 && $PartA <= 3159) ||
						($PartA >= 3337 && $PartA <= 3349) ||						
						($PartA >= 3528 && $PartA <= 3589))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 16)
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
					break;
					
				case 4:
					//Carte Blanche
					if (($PartA >= 3890 && $PartA <= 3899))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 14)
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
					break;


				case 5:
					//Visa
					if (($PartA >= 4000 && $PartA <= 4999))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 16 || $NumberLength == 13)
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
					break;
					
				case 6:
					//MasterCard
					if (($PartA >= 5100 && $PartA <= 5599))
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 16)
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
					break;					
					
				case 7:
					//Australian BankCard
					if ($PartA  == 5610)
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 16)
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
					break;					
										
				case 8:
					//Discover/Novus
					if ($PartA  == 6011)
					{
						//We have a valid PartA, is the length correct?
						if ($NumberLength == 16)
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
					break;					

				default:
					$returnValue = false;
					break;
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