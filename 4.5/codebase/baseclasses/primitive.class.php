<?php 

class Primitive
{
	public function __toString()
	{
		$returnValue = PrimitiveToString::_($this);

		return $returnValue;
	}

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue = $this->$getter();
		}
		else
		{
			$fields = $this->GenerateFieldList();

			if (array_key_exists(strtolower($Name), $fields))
			{
				$fieldName = $fields[strtolower($Name)];
				$returnValue = $this->$fieldName;
			}
			else
			{
				throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
			}
		}

		return $returnValue;
	}

	public function __set($Name, $Value)
	{
		$setter='set'.$Name;

		if(method_exists($this,$setter))
		{
			$this->$setter($Value);
		}
		else
		{
			$fields = $this->GenerateFieldList();

			if (array_key_exists(strtolower($Name), $fields))
			{
				$fieldName = $fields[strtolower($Name)];
				$this->$fieldName = $Value;
			}
			else if(method_exists($this,'get'.$Name))
			{
				throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
			}
			else
			{
				throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
			}
		}
	}

	public function __call($Name, $Parameters)
	{
		// This will only fire if an undefined method is called.
		throw new InvalidMethodException("No Public Method: $Name()", get_class($this), $Name);
	}
	
	protected function GenerateFieldList()
	{
		static $returnValue = Array();

		if (count($returnValue) == 0)
		{
			$fields = get_class_vars(get_class($this));

			$fieldNames = array_keys($fields);

			foreach ($fieldNames as $tempFieldName)
			{
				$key = strtolower(str_replace("_","",$tempFieldName));

				$returnValue[$key] = $tempFieldName;	
			}
		}
		return $returnValue;
	}

	public function HasProperty($Name)
	{
		$returnValue = false;

		if (method_exists($this,'get'.$Name) || method_exists($this,'set'.$Name))
		{
			$returnValue = true;
		}
		else
		{
			$fields = $this->GenerateFieldList();

			if (array_key_exists(strtolower($Name), $fields))
			{
				$returnValue = true;
			}
		}

		return $returnValue;

	}

	public function getAllProperties()
	{
		return get_object_vars($this);
	}


}
