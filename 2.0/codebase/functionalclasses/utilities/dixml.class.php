<?php
/**
 * XML Functions Abstract Class File
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

class DIxml extends Module
{
	
	static function ArrayToXML($SourceArray, $GroupName = null)
	{
		
		if (is_array($SourceArray) && count($SourceArray) > 0)
		{
		
				
			if (is_set($GroupName) == false)
			{
				$GroupName = "xml";				
			}
			
			//Open the group
			$returnValue = "<{$GroupName}>";
			
			foreach($SourceArray as $tempKey=>$tempValue)
			{
				if (is_array($tempValue))
				{
					$returnValue .= DIxml::ArrayToXML($tempValue, $tempKey);
				}
				else 
				{
					$returnValue .= "<{$tempKey}>{$tempValue}</{$tempKey}>";	
				}
			}

			//Close the group
			$returnValue .= "</{$GroupName}>";

		}
		else 
		{
			$returnValue = null;
		}
		
		return $returnValue;
	}
	
	static function XMLtoArray($SourceXML)
	{
		
		if ($SourceXML instanceof SimpleXMLElement) 
		{
   			$children = $SourceXML->children();
	  	}
	  	else
	  	{	  		
			$xml = new SimpleXMLElement($SourceXML);
   			$children = $xml->children();	
	  	}
	  	
		foreach ($children as $element => $value) 
		{
			if ($value instanceof SimpleXMLElement) 
			{
				$values = (array)$value->children();
				
				if (count($values) > 0) 
				{
					$returnValue[$element] = DIxml::XMLToArray($value);
				} 
				else 
				{
					if (!is_set($returnValue[$element])) 
					{
						$returnValue[$element] = (string)$value;
					} 
					else 
					{
						if (!is_array($returnValue[$element])) 
						{
							$returnValue[$element] = array($returnValue[$element], (string)$value);
						} 
						else 
						{
							$returnValue[$element][] = (string)$value;
						}
					}
				}
			}
		}
 		
		return $returnValue;
		
	}
	
}
?>