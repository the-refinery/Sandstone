<?php
/**
 * GUI Class File
 * @package Sandstone
 * @subpackage GUI
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class GUI extends Component
{
	/**
		* Returns $Value as a URL friendly string.  According
		* to W3C allowed URL characters
		*/
		public static function GetSEOFriendly($Value)
		{
			// Remove Reserved Characters
			$Value = str_replace("$", "", $Value);
			$Value = str_replace("&", "", $Value);
			$Value = str_replace("+", "", $Value);
			$Value = str_replace(",", "", $Value);
			$Value = str_replace("/", "", $Value);
			$Value = str_replace(":", "", $Value);
			$Value = str_replace(";", "", $Value);
			$Value = str_replace("=", "", $Value);
			$Value = str_replace("?", "", $Value);
			$Value = str_replace("@", "", $Value);
			
			// Characters that need escaped we simply remove.
			$Value = str_replace(" ", "-", $Value);
			$Value = str_replace("\"", "", $Value);
			$Value = str_replace("<", "", $Value);
			$Value = str_replace(">", "", $Value);
			$Value = str_replace("#", "", $Value);
			$Value = str_replace("%", "", $Value);
			$Value = str_replace("{", "", $Value);
			$Value = str_replace("}", "", $Value);
			$Value = str_replace("|", "", $Value);
			$Value = str_replace("\\", "", $Value);
			$Value = str_replace("^", "", $Value);
			$Value = str_replace("~", "", $Value);
			$Value = str_replace("[", "", $Value);
			$Value = str_replace("]", "", $Value);
			$Value = str_replace("`", "", $Value);
			$Value = str_replace("'", "", $Value);
			
			// Just to be extra safe, let's encode anything that 
			// may have slipped through
			$returnValue = urlencode($Value);
					
			return $returnValue;
		}
}

?>