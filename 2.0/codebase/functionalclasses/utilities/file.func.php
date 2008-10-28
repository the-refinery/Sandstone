<?php
/**
 * File Function File
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


function ReadFileContents($FileSpec, $UseIncludeFilePath = false, $IncludeBlankLines = false)
{
	
	$handle = @fopen($FileSpec, "r", $UseIncludeFilePath);
	
	if ($handle) 
	{
		while (!feof($handle)) 
		{
			$buffer = fgets($handle, 4096);
			
			if (strlen($buffer) > 0)
			{
				$returnValue[] = rtrim($buffer);	
			}
			else 
			{
				if ($IncludeBlankLines == true)
				{
					$returnValue[] = rtrim($buffer);
				}
			}
		}
	   
	   fclose($handle);
	}
	else 
	{
		$returnValue = null;	
	}
	
	return $returnValue;
}

?>