<?php
/*
DI Array Class File
@package Sandstone
@subpackage Utilities
*/


class DIarray extends ArrayObject
{
    public function __toString()
    {

		$divColor = "#fcc";
		$liColor = "#ffc";
		$liBorder = "#fcc";

        $randomID = rand();
        $anchorID = "DIarray_{$randomID}";

        $detailJS = "    document.getElementById('{$anchorID}_summary').style.display = 'none';
                        document.getElementById('{$anchorID}_detail').style.display = 'block';";

        $summaryJS = "    document.getElementById('{$anchorID}_detail').style.display = 'none';
                        document.getElementById('{$anchorID}_summary').style.display = 'block';";


        $elementCount = count($this);


        $returnValue = "<a id=\"{$anchorID}\"></a>";

        //Summary DIV
        $returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: {$divColor}; padding: 6px;\">";
        $returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>DI Array - Count = {$elementCount}</b></a>";
        $returnValue .= "</div>";

        //Detail DIV
        $returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: {$divColor}; padding: 6px; display:none;\">";
        $returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">DI Array</h1>";
        $returnValue .= "<h2 style=\"padding: 0; margin: 5px 0 5px 10px;\">Count: {$elementCount}</h2>";

        $returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

        foreach ($this as $key=>$value)
        {
            $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";

            if (is_numeric($key) == false)
            {
                $key = "'{$key}'";
            }

            $returnValue .= "<strong>[ {$key} ]: </strong> ";

            if ($value instanceof DIarray)
            {
                $returnValue .= $value->__toString();
            }
            elseif (is_object($value))
            {

				$methods = get_class_methods($value);

				if (array_search("__toString", $methods) !== false)
				{
					$returnValue .= $value->__toString();
				}
				else
				{
					$returnValue .= get_class($value) . " object";
				}
            }
            else
            {
                if (is_string($value))
                {
                    $returnValue .= "\"{$value}\"";
                }
                else
                {
                    $returnValue .= "{$value}";
                }
            }

            $returnValue .= "</li>";
        }

        $returnValue .= "</ul>";

        $returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$summaryJS}\">Close</a>";

        $returnValue .= "</div>";

        return $returnValue;
    }

	public function StringDump()
	{
		//Get the vardump of the array
		ob_start();
		var_dump($this);
		$returnValue = ob_get_contents();
		ob_end_clean();

		//strip off the leading stuff
		$returnValue = substr($returnValue, strpos($returnValue, "{"));

		return $returnValue;
	}

	public function Keys()
	{

		$returnValue = Array();

		foreach ($this as $key=>$value)
		{
			$returnValue[] = $key;
		}

		return $returnValue;
	}

	public function Clear()
	{
		$this->ExchangeArray(Array());
	}

	public function Destroy()
	{
		foreach ($this as $tempElement)
		{
			if ($tempElement instanceof EntityBase || $tempElement instanceof DIarray)
			{
				$tempElement->Destroy();
			}
		}
	}

    static public function ImplodeAssoc($KeyToValueGlue, $ElementToElementGlue, $Array, $IsValueQuoted = false)
	{
		if ($IsValueQuoted == false)
		{
		    foreach($Array as $key => $value)
			{
				$newArray[] = $key . $KeyToValueGlue . $value;
			}
		}
		else
		{
			foreach($Array as $key => $value)
			{
				$newArray[] = $key . $KeyToValueGlue . "'$value'";
			}
		}

	    return implode($ElementToElementGlue, $newArray);
	}

	static public function SortByObjectProperty($SourceArray, $PropertyName, $IsDecending = false, $IsMaintainKeys = true)
    {
		$valuesArray = Array();

		foreach ($SourceArray as $key=>$value)
		{
			$valuesArray[$key] = $value->$PropertyName;
		}

		if ($IsDecending)
		{
			arsort($valuesArray);
		}
		else
		{
			asort($valuesArray);
		}

		$returnValue = new DIarray();

		foreach ($valuesArray as $key=>$value)
		{
			if ($IsMaintainKeys)
			{
				$returnValue[$key] = $SourceArray[$key];
			}
			else
			{
				$returnValue[] = $SourceArray[$key];
			}
		}

		return $returnValue;
	}

	static public function ForceLowercaseKeys($SourceArray, $IsRecursive=true)
	{

		//Make sure we return either a DIarray or standard array based
		//on what the source array is.
		if ($SourceArray instanceof DIarray)
		{
			$returnValue = new DIarray();
		}
		else
		{
			$returnValue = Array();
		}

		foreach ($SourceArray as $key=>$value)
		{
			if (is_array($value) && $IsRecursive)
			{
				$returnValue[strtolower($key)] = DIarray::ForceLowercaseKeys($value, true);
			}
			else
			{
				$returnValue[strtolower($key)] = $value;
			}
		}

		return $returnValue;

	}

	static public function array_merge($Array1, $Array2)
	{
		$returnValue = new DIarray();

		foreach ($Array1 as $key=>$value)
		{
			$returnValue[$key] = $value;
		}

		foreach ($Array2 as $key=>$value)
		{
			$returnValue[$key] = $value;
		}

		return $returnValue;
	}
}

?>