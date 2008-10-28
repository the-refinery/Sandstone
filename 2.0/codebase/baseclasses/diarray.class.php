<?php
/**
 * DI Array Class File
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


class DIarray extends ArrayObject
{

	public function Keys()
	{

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

    static public function ImplodeAssoc($Glue1, $Glue2, $Array, $IsValueQuoted = false)
	{
		if ($IsValueQuoted = false)
		{
		    foreach($Array as $key => $value)
			{
				$newArray[] = $key . $Glue1 . $value;
			}			
		}
		else
		{
			foreach($Array as $key => $value)
			{
				$newArray[] = $key . $Glue1 . "'$value'";
			}
		}

	    return implode($Glue2, $newArray);
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
}

?>
