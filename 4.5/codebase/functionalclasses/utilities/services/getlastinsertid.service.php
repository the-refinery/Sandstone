<?php

class GetLastInsertID extends Service
{
	static public function _()
	{
		$query = new Query();

		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$returnValue = $query->SingleRowResult['newID'];

		return $returnValue;
	}
}
