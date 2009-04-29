<?php
/*
 CreditTransaction Class File
 
 @package Sandstone
 @subpackage CreditCard

 */

class CreditTransaction extends BaseCreditCardTransaction
{

	static public function GenerateBaseWhereClause()
	{
		$returnValue = parent::GenerateBaseWhereClause();

		$returnValue .= "AND	a.CreditCardTransactionTypeID = 3 ";

		return $returnValue;
	}
}
?>
