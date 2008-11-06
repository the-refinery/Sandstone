<?php
/*
ObjectSet Class File

@package Sandstone
@subpackage ObjectSet
*/

Namespace::Using("Sandstone.Date");

class ObjectSet extends Module
{

	protected $_objects;
	protected $_keys;

	public $_currentIndex;

	protected $_name;
	protected $_timestamp;

	public function __construct($Results = null, $ClassName = null, $KeyProperty = null)
	{
		$this->_objects = array();
		$this->_keys = Array();
		$this->_currentIndex = 0;

		if (is_set($Results) && is_set($ClassName) && is_set($KeyProperty))
		{
			$this->Load($Results, $ClassName, $KeyProperty);
		}

	}

	/*
	Name property

	@return string
	@param string $Value
	*/
	public function getName()
	{
		return $this->_name;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/*
	Timestamp property

	@return Date
	*/
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/*
	Count property

	@return integer
	*/
	public function getCount()
	{
		if ($this->_isLoaded)
		{
			$returnValue = Count($this->_objects);
		}
		else
		{
			$returnValue = 0;
		}

		return $returnValue;

	}

	/*
	CurrentItem property

	@return object
	*/
	public function getCurrentItem()
	{
		if ($this->_isLoaded)
		{
			if (! $this->EOF)
			{
				$returnValue =  $this->_objects[$this->_keys[$this->_currentIndex]];
			}
			else
			{
				$returnValue = null;
			}
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;

	}

	/*
	ItemsByKey property

	@return array
	*/
	public function getItemsByKey()
	{
		if ($this->_isLoaded)
		{
			$returnValue =  $this->_objects;
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	/*
	ItemsByIndex property

	@return array
	*/
	public function getItemsByIndex()
	{
		if ($this->_isLoaded)
		{
			foreach($this->_keys as $index=>$key)
			{
				$returnValue[$index] = $this->_objects[$key];
			}
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	public function getEOF()
	{
		if ($this->_isLoaded)
		{
			if ($this->_currentIndex == $this->getCount())
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function Load($Restuls, $ClassName, $KeyProperty)
	{

		if (count($Results) > 0)
		{
			//We'll start with a false return value, and then
			//set it to true after our first successful load of
			//an object.
			$returnValue = false;

			foreach ($Results as $dr)
			{
				$tempObject = new $ClassName($dr);

				if ($tempObject->IsLoaded)
				{
					$key = $tempObject->$KeyProperty;

					$this->AddItem($key, $tempObject);

					$returnValue = true;
				}

			}

			if ($returnValue == true)
			{
				//If we haven't had a name set already
				//generate one
				if (is_set($this->_name) == false)
				{
					$this->_name = "{$ClassName}-List";
				}

				$this->_isLoaded = true;
			}
		}
		else
		{
			$returnValue = false;
		}

		$this->_currentIndex = 0;

		$this->_isLoaded = $returnValue;

		return $returnValue;
	}

	public function AddItem($Key, $Object)
	{
		$this->_keys[] = $Key;
		$this->_objects[$Key] = $Object;

		if (is_set($this->_timestamp) == false)
		{
			$this->_timestamp = new Date();
		}
	}

	public function FetchItem()
	{
		if ($this->_isLoaded)
		{
			$returnValue = $this->getCurrentItem();

			if (is_set($returnValue))
			{
				$this->_currentIndex++;
			}
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	public function MoveFirst()
	{
		$this->_currentIndex = 0;
	}

	public function MoveNext()
	{
		if ($this->_isLoaded)
		{
			if ($this->_currentIndex < $this->getCount())
			{
				$this->_currentIndex++;
			}
		}
	}

	public function MovePrevious()
	{
		if ($this->_isLoaded)
		{
			if ($this->_currentIndex > 0 )
			{
				$this->_currentIndex--;
			}
		}
	}

	public function MoveLast()
	{
		if ($this->_isLoaded)
		{
			$this->_currentIndex = $this->getCount() - 1;
		}
	}

}
?>