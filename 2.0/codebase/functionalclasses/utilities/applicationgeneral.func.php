<?php
/**
 * General Application Function File
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

function is_set($Value)
{

	//This function exists to provide us the standard "is_set" type functionality
	//but in a manner that works with class properties rendered through the magic getter function.
	if (is_null($Value))
	{
		$returnValue = false;
	}
	else 
	{
		$returnValue = true;
	}
	
	return $returnValue;
	
}

function di_var_dump($Value, $IsDie = false)
{
	//This function is used to easily give a nicely formatted var_dump with an optional die.

	echo "<pre>";
	var_dump($Value);
	echo "</pre>";

	if ($IsDie)
	{
		die();
	}

}

function di_watch($String, $IsDie = false)
{
	static $a = 1;
	
	echo "<br />";
	echo "> " . $a .": " . $String;
	echo "<br />";
	
	if ($IsDie)
	{
		die();
	}
	
	$a++;
}

function di_break($IsDie = false)
{	
	$backTrace = debug_backtrace();
	
	// use $backTrace[0] for File and line number... not sure why, but you have to. :-D
	$fileName = basename($backTrace[0]['file']);
	$lineNumber = $backTrace[0]['line'];
	$functionName = $backTrace[1]['function'];
	$objectClassName = $backTrace[1]['class'];
	
	echo "<div style=\"padding:4px; background:#ffc; border:1px solid #fcc; margin:4px;\">";
	echo "<span style=\"display:block; font-size:16px; font-weight:normal; float:left;\"><strong>Line: $lineNumber</strong> - $fileName</span>";
	echo "<span style=\"float:right; font-style:italic;\">" . $objectClassName . "->" . $functionName . "();</span>";
	echo "<div style=\"clear:both;\"></div>";
	echo "</div>";
	
	if ($IsDie)
	{
		die();
	}
	
	$a++;
}

?>