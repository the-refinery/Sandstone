<?php
/*
CreditCardType Class File

@package Sandstone
@subpackage CreditCard
 */

class CreditCardType extends EntityBase
{

	public function __construct($ID = null)
	{

		$this->_isMessagesDisabled = true;
		$this->_isTagsDisabled = true;

		parent::__construct($ID);
	}

	protected function SetupProperties()
	{

		//AddProperty Parameters:
		// 1) Name
		// 2) DataType
		// 3) DBfieldName
		// 4) IsReadOnly
		// 5) IsRequired
		// 6) IsPrimaryID
		// 7) IsLoadedRequired
		// 8) IsLoadOnDemand
		// 9) LoadOnDemandFunctionName

		$this->AddProperty("CardTypeID","integer","CardTypeID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("IsAccepted","boolean","IsAccepted",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_CreditCardTypeMaster
						(
							Name,
							IsAccepted
						)
						VALUES
						(
							{$query->SetTextField($this->_name)},
							{$query->SetBooleanField($this->_isAccepted)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_CreditCardTypeMaster SET
							Name = {$query->SetTextField($this->_name)},
							IsAccepted = {$query->SetBooleanField($this->_isAccepted)}
						WHERE CardTypeID = {$this->_cardTypeID}";

		$query->Execute();

		return true;
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

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.CardTypeID,
										a.Name,
										a.IsAccepted ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_CreditCardTypeMaster a ";

		return $returnValue;
	}

	static public function GenerateBaseWhereClause()
	{
		return null;

	}

	static public function LookupAll()
	{

		$query = new Query();

		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = self::GenerateBaseWhereClause();

		$orderByClause = "ORDER BY a.Name ";

		$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause;

		$query->Execute();

		$returnValue = new ObjectSet($query, "CreditCardType", "CardTypeID");

		return $returnValue;
	}



}
?>