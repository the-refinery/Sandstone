<?php
/*
General Application Function File

@package Sandstone
@subpackage Utilities
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
	static $breakCounter = 1;

	$backTrace = debug_backtrace();

	// use $backTrace[0] for File and line number... not sure why, but you have to. :-D
	$fileName = basename($backTrace[0]['file']);
	$lineNumber = $backTrace[0]['line'];
	$functionName = $backTrace[1]['function'];
	$objectClassName = $backTrace[1]['class'];

	echo "<div style=\"padding:4px; background:#ffc; border:1px solid #fcc; margin:4px;\">";
	echo "<span style=\"display:block; font-size:16px; font-weight:normal; float:left;\"><strong>Line: $lineNumber</strong> - $fileName</span>";
	echo "<span style=\"float:right; font-style:italic;\">" . $breakCounter .": " . $String . "</span>";
	echo "<div style=\"clear:both;\"></div>";
	echo "</div>";

	if ($IsDie)
	{
		die();
	}
	else
	{
		//Force the output
		ob_flush();
		flush();
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

	$parts = explode(" ", microtime());
	$timestamp = substr($parts[1], -2) . substr($parts[0], 1, 6);

	echo "<div style=\"padding:4px; background:#ffc; border:1px solid #fcc; margin:4px;\">";
	echo "<span style=\"display:block; font-size:16px; font-weight:normal; float:left;\"><strong>Line: $lineNumber</strong> - $fileName</span>";
	echo "<span style=\"float:right; font-style:italic;\">" . $objectClassName . "->" . $functionName . "(); [$timestamp]</span>";
	echo "<div style=\"clear:both;\"></div>";
	echo "</div>";

	if ($IsDie)
	{
		die();
	}

	$a++;
}

function di_console_log($String, $Type="info")
{
	$Type = strtolower($Type);

	echo "<script type=\"text/javascript\">";

	switch ($Type)
	{
		case 'warn':
			echo "console.warn(\"{$String}\");";
			break;

		case 'error':
			echo "console.error(\"{$String}\");";
			break;

		case 'info':
		default:
			echo "console.info(\"{$String}\");";
			break;
	}

	echo "</script>";
}

function di_stacktrace($IsDie = false)
{
	throw new ShowStackTraceException("Stack Trace Output");
}

function di_timer()
{
	static $startTime = null;

	if (is_set($startTime))
	{
		$endTime = microtime(true);

		$et = $endTime - $startTime;

		$returnValue = $et;

		$startTime = null;
	}
	else
	{
		//We are starting a timer
		$startTime = microtime(true);
	}
	
	return $returnValue;
}


function diecho($Value)
{
    echo $Value;
    die();
}

function di_echo($Value)
{
	echo $Value;
	die();
}

?>