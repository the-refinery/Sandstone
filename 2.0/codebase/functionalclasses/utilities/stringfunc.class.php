<?php
/**
 * String Functions Abstract Class File
 * @package Sandstone
 * @subpackage Utilities
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class StringFunc
{
	
	/**
	 * Removes all non-numeric characters from a string.
	 *
	 * @return void
	 **/
	static function MakeDecimal($String)
	{
		$ReturnValue = ereg_replace('[^0-9.]', '', $String);
		
		return $ReturnValue;
	}
	
	static function FormatCurrency($Value)
	{
		return "\$" . number_format($Value,2);
	}
	
	static function FormatNumber($Value, $DecimalPlaces = 0)
	{
		return number_format($Value, $DecimalPlaces);
	}
}


?>