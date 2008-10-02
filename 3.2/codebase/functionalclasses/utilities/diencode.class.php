<?php
/*
Encoding Functions Abstract Class File

@package Sandstone
@subpackage Utilities
*/

class DIencode extends Module
{
	static function UUEncode($Data)
	{
		return convert_uuencode($Data);
	}
	
	static function UUDecode($Data)
	{
		return convert_uudecode($Data);
	}
}

?>