<?php
/*
Encryption Functions Abstract Class File

@package Sandstone
@subpackage Utilities
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
		
		$returnValue = DIencrypt::bin2hex($fullEncode);
		
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
	
	/*
	This function exists solely as a wrapper so that the signature is the
	same as our hex2bin custom method.
	*/
	static function bin2hex($BinaryData)
	{
		return bin2hex($BinaryData);
	}
	
	static function hex2bin($HexData)
	{
		$corrected = ereg_replace("[^0-9a-fA-F]","",$HexData);
		
		$returnValue = pack("H".strlen($corrected), $corrected); 
		
   		return $returnValue;
	}
}


?>