<?php
/*
Entity Children Class File

@package Sandstone
@subpackage EntityBase
*/

class EntityChildren extends Module implements ArrayAccess, Iterator
{

	protected $_targetClass;

	protected $_parentObject;
	protected $_parentProperty;

    protected $_keys;
    protected $_elements;

    protected $_nextAutoKey;

    protected $_currentIndex;

    public function __construct()
    {
    	$this->Clear();
    }

	public function Destroy()
	{
		$this->_parentObject = null;
		$this->_elements = null;
		$this->_keys = null;
	}

    public function __toString()
    {

		$divColor = "#fc9";
		$liColor = "#ffc";
		$liBorder = "#fcc";

        $randomID = rand();
        $anchorID = "EntityChildren_{$randomID}";

        $detailJS = "    document.getElementById('{$anchorID}_summary').style.display = 'none';
                        document.getElementById('{$anchorID}_detail').style.display = 'block';";

        $summaryJS = "    document.getElementById('{$anchorID}_detail').style.display = 'none';
                        document.getElementById('{$anchorID}_summary').style.display = 'block';";


        if (is_set($this->_elements))
        {
        	$elementCount = count($this->_elements);
        }
        else
        {
        	$elementCount = 0;
        }


        $returnValue = "<a id=\"{$anchorID}\"></a>";

        //Summary DIV
        $returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: {$divColor}; padding: 6px;\">";
        $returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>Entity Children ({$this->_targetClass}) - Count = {$elementCount}</b></a>";
        $returnValue .= "</div>";

        //Detail DIV
        $returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: {$divColor}; padding: 6px; display:none;\">";
        $returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">Entity Children</h1>";
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
                $returnValue .= $value->__toString();
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

	public function getCount()
	{
		return count($this->_elements);
	}

	public function Load($TargetClass, $ds, $ParentObject = null, $ParentProperty = null, $KeyField = null)
	{

		$this->Clear();

        $this->_targetClass = $TargetClass;
		$this->_parentObject = $ParentObject;
		$this->_parentProperty = $ParentProperty;


		//Get the ID field
		if (is_set($KeyField) == false)
		{
			$tempEntity = new $TargetClass ();
			$KeyField = $tempEntity->PrimaryIDproperty->DBfieldName;
		}

		//Load our elements from the dataset
        while ($dr = $ds->FetchRow())
        {
			$key = $dr[$KeyField];
			$this->offsetSet($key, $dr);
        }

	}

    public function offsetExists($Offset)
    {

        $returnValue = false;

        if (in_array($Offset, $this->_keys))
        {
            $returnValue = true;
        }

        return $returnValue;
    }

    public function offsetGet($Offset)
    {

		if (is_set($this->_targetClass))
		{
			$className = $this->_targetClass;

			$returnValue = new $className ($this->_elements[$Offset]);

			if (is_set($this->_parentProperty))
			{
				$propertyName = $this->_parentProperty;
				$returnValue->$propertyName = $this->_parentObject;
			}
		}

        return $returnValue;
    }

    public function offsetSet($Offset, $Value)
    {
        if (is_set($Offset))
        {
            $this->_elements[$Offset] = $Value;
            $this->_keys[] = $Offset;
        }
        else
        {

            while (array_key_exists($this->_nextAutoKey, $this->_elements))
            {
                $this->_nextAutoKey++;
            }

            $this->_elements[$this->_nextAutoKey] = $Value;
            $this->_keys[] = $this->_nextAutoKey;

            $this->_nextAutoKey++;
        }
    }

    public function offsetUnset($Offset)
    {
        unset($this->_elements[$Offset]);
    }

    public function current()
    {
        $currentKey = $this->_keys[$this->_currentIndex];

        $returnValue = $this->offsetGet($currentKey);

        return $returnValue;
    }

    public function key()
    {
        return $this->_keys[$this->_currentIndex];
    }

    public function next()
    {
        $this->_currentIndex++;
    }

    public function rewind()
    {
        $this->_currentIndex = 0;
    }

    public function valid()
    {
        $returnValue = false;

        if (is_array($this->_keys))
        {
            if (array_key_exists($this->_currentIndex, $this->_keys))
            {
                $returnValue = true;
            }
        }

        return $returnValue;
    }

	public function Clear()
	{
    	$this->_elements = Array();
        $this->_keys = Array();

        $this->_nextAutoKey = 0;
        $this->_currentIndex = 0;
	}

	public function KeyExists($Key)
	{
		return $this->offsetExists($Key);
	}

}
?>