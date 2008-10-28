<?php
/**
 * Encryption Functions Abstract Class File
 * @package Sandstone
 * @subpackage Utilities
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

NameSpace::Using("Sandstone.Registry");

class DIencrypt
{

	static function Encrypt($Data)
	{
		$registry = Application::Registry();
		$key = $registry->PasswordPrefix;
		
		$fullEncode = '';
		
		for ($i = 1; $i <= strlen($Data); $i++)
		{
			$character = substr($Data, $i - 1, 1);
			$keycharacter = substr($key, ($i % strlen($key)) - 1, 1);
			$character = chr( ord($character) + ord($keycharacter) );
			$fullEncode .= $character;
		}
		
		$returnValue = bin2hex($fullEncode);
		
		return $returnValue;
	}

	static function Decrypt($Data)
	{
		$registry = Application::Registry();
		$key = $registry->PasswordPrefix;
		
		$Data = DIencrypt::hex2bin($Data);
		
		$returnValue = '';
		
		for ($i = 1; $i <= strlen($Data); $i++)
		{
			$character = substr($Data, $i-1, 1);
			$keycharacter = substr($key, ($i % strlen($key))-1, 1);
			$character = chr(ord($character)-ord($keycharacter));
			$returnValue .= $character;
		}
				
		//Trim any trailing nulls
		$returnValue = rtrim($returnValue, "\0");
		
		return $returnValue;
	}
	
	static function hex2bin($HexData)
	{
		$corrected = ereg_replace("[^0-9a-fA-F]","",$HexData);
		
		$returnValue = pack("H".strlen($corrected), $corrected); 
		
   		return $returnValue;
	}
}


?>