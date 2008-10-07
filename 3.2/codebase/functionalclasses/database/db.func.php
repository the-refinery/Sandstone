<?php
/*
Database Function File
@package Sandstone
@subpackage Database

This function creates an ADODB database object.   By changing the connection type,
you can use any database engine supported by ADODB.

Defaults to MySQL.

@return object ADODB connection object
*/
function GetConnection($ConfigArray = null)
{
	if (is_array($ConfigArray) || $ConfigArray instanceof DIarray)
	{
		//We were passed an array - use it.
		$dbConfig = $ConfigArray;
	}
	else if (is_set($ConfigArray))
	{
		//We were passed a config name, check to see if we have settings
		$dbConfig = Application::Registry()->$ConfigArray;

		if (is_set($dbConfig) == false)
		{
			//Didn't find one, default to the application
			$dbConfig = Application::Registry()->ApplicationDB;
		}

	}
	else
	{
		//None specified, use the application
		$dbConfig = Application::Registry()->ApplicationDB;
	}


	$conn = Application::DatabaseConnection($dbConfig);
	$returnValue = new Connection($conn);

	return $returnValue;

}


?>