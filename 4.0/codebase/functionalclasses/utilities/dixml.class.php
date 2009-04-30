<?php
/*
XML Functions Abstract Class File

@package Sandstone
@subpackage Utilities
*/

class DIxml extends Module
{

	static public function ArrayToXML($SourceArray, $GroupName = null, $Key = null, $IsTopLevelIncluded = true, $IsFormatted = true, $IsDeclarationIncluded = false, $XMLversion = "1.0", $Encoding = "ISO-8859-1", $GroupAttributes = Array())
	{

		$returnValue = DIxml::ArrayToXMLprocessing($SourceArray, $GroupName, $Key, $IsTopLevelIncluded, $IsFormatted);

		if ($IsDeclarationIncluded)
		{
			$declaration = "<?xml version=\"{$XMLversion}\" encoding=\"{$Encoding}\"?>";

			if ($IsFormatted)
			{
				$declaration .= "\n";
			}

			$returnValue = $declaration . $returnValue;
		}

		if (count($GroupAttributes) > 0)
		{
			foreach($GroupAttributes as $key=>$value)
			{
				$attributes .= " {$key}=\"{$value}\"";
			}

			$returnValue = str_replace("<{$GroupName}>", "<{$GroupName}{$attributes}>", $returnValue);
		}


		return $returnValue;
	}

	static protected function ArrayToXMLprocessing($SourceArray, $GroupName = null, $Key = null, $IsTopLevelIncluded = true, $IsFormatted = true, $TabCount = 0)
	{

		if ((is_array($SourceArray) || $SourceArray instanceof DIarray) && count($SourceArray) > 0)
		{

            //If the top level group isn't included,
            //we subtract 1 from the TabCount
            if ($IsTopLevelIncluded == false)
            {
                $TabCount--;
            }

            if ($IsFormatted)
            {
                $newLine = "\n";
                $singleTab = "\t";

                for ($i = 1; $i <= $TabCount; $i++)
                {
                    $tabs .= $singleTab;
                }
            }

			if (is_set($GroupName) == false)
			{
				$GroupName = "xml";
			}

			//Open the group (if required)
            if ($IsTopLevelIncluded)
            {
                $returnValue = "{$tabs}<{$GroupName}>{$newLine}";
            }

			foreach($SourceArray as $tempKey=>$tempValue)
			{
				if (is_set($Key))
				{
					$key = $Key;
				}
				else
				{
					$key = $tempKey;
				}

				if (is_array($tempValue) || $tempValue instanceof DIarray)
				{
					$returnValue .= DIxml::ArrayToXMLprocessing($tempValue, $key, null, true, $IsFormatted, $TabCount + 1);
				}
				else
				{
					if (strlen($tempValue) > 0)
					{
						$returnValue .= "{$tabs}{$singleTab}<{$key}>{$tempValue}</{$key}>{$newLine}";
					}
					else
					{
						$returnValue .= "{$tabs}{$singleTab}<{$key}/>{$newLine}";
					}

				}
			}

			//Close the group (if required)
            if ($IsTopLevelIncluded)
            {
			    $returnValue .= "{$tabs}</{$GroupName}>{$newLine}";
            }
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	static public function XMLtoArray($SourceXML)
	{

		if ($SourceXML instanceof SimpleXMLElement)
		{
   			$children = $SourceXML->children();
	  	}
	  	else
	  	{
			$xml = new SimpleXMLElement($SourceXML, LIBXML_NOWARNING);
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

    static public function XMLtoDIarray($SourceXML, $IsRootElementIncluded = true)
    {

        $returnValue = new DIarray();
        $documentChildren = new DIarray();

        $processedElementNames = new DIarray();

        //Get the children from a SimpleXMLElement
        if ($SourceXML instanceof SimpleXMLElement)
        {
        	$xml = $SourceXML;
            $children = $SourceXML->children();
        }
        else
        {
            $xml = new SimpleXMLElement($SourceXML);
            $children = $xml->children();
        }

        //Loop the children and process them
        foreach ($children as $element => $value)
        {
            if ($value instanceof SimpleXMLElement)
            {
                //Have we already processed an element with this name?
                if (array_key_exists($element, $processedElementNames))
                {
                    //If there is only 1 existing value, we will need to convert to an array
                    if ($processedElementNames[$element] == 1)
                    {
                        $conversionArray = new DIarray();
                        $conversionArray[] = $documentChildren[$element];

                        $documentChildren[$element] = $conversionArray;
                    }

                    $processedElementNames[$element]++;
                    $isExistingElement = true;
                }
                else
                {
                    $processedElementNames[$element] = 1;
                    $isExistingElement = false;
                }


                //Pull the values for this element
                $values = (array)$value->children();


                //Is this a single value or does it have children?
                //This builds the specific value to add to our output array
                if (count($values) > 0)
                {
                    //There are child elements
                    $valueToAdd = DIxml::XMLToDIArray($value, false);
                }
                else
                {
                    //Single Value
                    $valueToAdd = (string)$value;
                }


                //Finally add it to our output array as appropriate
                if ($isExistingElement)
                {
                    //We have existing value(s) of this element
                    $documentChildren[$element][] = $valueToAdd;
                }
                else
                {
                     //This is the first instance of this element
                    $documentChildren[$element] = $valueToAdd;
                }
            }
        }

		if ($IsRootElementIncluded)
		{
			$returnValue[$xml->getName()] = $documentChildren;
		}
		else
		{
			$returnValue = $documentChildren;
		}

        return $returnValue;

    }

}
?>
