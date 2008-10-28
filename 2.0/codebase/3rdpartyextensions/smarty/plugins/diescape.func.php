<?php
/**
 * Smarty plugin
 * @package Sandstone
 * @subpackage Smarty
 */

/**
 * Escape string modifier plugin
 **/
function DIescape($string)
{
	if (! is_object($string) && ! is_array($string))
	{
        $returnValue = htmlentities($string);
	}
	else
	{
		$returnValue = $string;
	}

	return $returnValue;
}

function DIunescape($string)
{
	if (! is_object($string) && ! is_array($string))
	{
		if (strlen($string) > 0)
		{
			$returnValue = html_entity_decode($string);	
		}
		else 
		{
			$returnValue = $string;
		}
        
	}
	else
	{
		$returnValue = $string;
	}
	
	return $returnValue;
	
}

?>
