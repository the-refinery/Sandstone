<?php
/*
String Functions Abstract Class File

@package Sandstone
@subpackage Utilities
*/

class ColorFunc
{
	/*
	This returns a random hex color with the ability to limit the range of light/dark.
	*/
	static function GenerateRandomColor($Dark = 0, $Light = 255)
	{
		$red = mt_rand($Dark, $Light);
		$green = mt_rand($Dark, $Light);
		$blue = mt_rand($Dark, $Light);
		
	    $returnValue = sprintf("%02X%02X%02X", $red, $green, $blue);
	
		return $returnValue;
	}
}


?>