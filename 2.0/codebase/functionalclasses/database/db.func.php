<?php
/**
 * Database Function File
 * @package Sandstone
 * @subpackage Database
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

/**
 * This function creates an ADODB database object.   By changing the connection type, 
 * you can use any database engine supported by ADODB.
 * 
 * Defaults to MySQL.
 *
 * @return object ADODB connection object
 */
function GetConnection($ConfigArray = null)
{	
	
	if (is_set($ConfigArray))
	{
		$dbConfig = $ConfigArray;
	}
	else 
	{
		$dbConfig = Application::DBconfig();
	}

	$conn = Application::DatabaseConnection($dbConfig);

	$returnValue = new Connection($conn);
	
	return $returnValue;
	
}


?>