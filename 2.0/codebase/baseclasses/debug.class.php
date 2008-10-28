<?php

/* Contains Debug routines */
class Debug extends Component
{
	/**
	 * Show a blackbox view of the current object
	 **/
	public function Inspect()
	{
		$DebugObject = new ReflectionClass(get_class($this));
		$DebugModule = new ReflectionClass('module');
		
		$returnValue = "<fieldset style=\"background: #E5EEF5;\"><legend>". get_class($this) ."</legend>";
		
		$tempMethods = $this->GetDebugMethods($DebugObject);
		$tempProperties = $this->ExtractDebugProperties($tempMethods);
		
		// Display Properties
		$returnValue .= "<fieldset style=\"background: #ddd;\"><legend>Properties</legend><ul>";
		foreach ($tempProperties as $Key => $Value)
		{
			$returnValue .= "<li>" . $Key . "</li>";	
		}
		$returnValue .= "</ul></fieldset>";
		
		// Display Methods
		$returnValue .= "<fieldset style=\"background: #ddd;\"><legend>Methods</legend><ul>";
		
		foreach ($tempMethods as $Key => $Value)
		{
			if (substr($Key,0,2) != "__" && substr($Key,0,3) != "get" && substr($Key,0,3) != "set" && (! $DebugModule->hasMethod($Key)))
			{
				unset($Parameters);
				
				foreach ($Value as $Parameter)
				{
					$Parameters[] = $Parameter->getName();
				}
				
				if (is_array($Parameters))
				{
					$returnValue .= "<li>$Key(\$" . implode(", \$", $Parameters) .")</li>";	
				}
				else
				{
					$returnValue .= "<li>$Key()</li>";	
				}	
			}	
		}
		
		$returnValue .= "</ul></fieldset>";
		
		$returnValue .= "</fieldset>";
		
		return $returnValue;
	}
	
	/**
	 * Display a visual representation of the object.  This is used for debugging reasons 
	 * to check the integrity of the object structure.
	 *
	 * @return string Object Display
	 */
	public function __toString()
	{	
		$DebugObject = new ReflectionClass(get_class($this));
		
		$returnValue = "<fieldset style=\"background: #E5EEF5;\"><legend>". get_class($this) ."</legend>";
		
		// Display Protected Data
		$returnValue .= "<fieldset style=\"background: #ddd;\"><legend>Protected Data</legend><ul>";
		foreach ($this->GetDebugProtectedData($DebugObject) as $Key => $Value)
		{
			if (is_numeric($Value))
			{
				$returnValue .= "<li>" . $Key . ": " . $Value . "</li>";	
			}
			else 
			{
				if ($Value == "null" || $Value == "array()" || substr($Value, 0, 9) == "<fieldset")
				{
					$returnValue .= "<li>" . $Key . ": " . $Value . "</li>";	
				}
				else 
				{
					$returnValue .= "<li>" . $Key . ": \"" . $Value . "\"</li>";	
				}
			}
		}
		$returnValue .= "</ul></fieldset>";
		
		// Display Properties
		$returnValue .= "<fieldset style=\"background: #ddd;\"><legend>Properties</legend><ul>";
		
		$tempMethods = $this->GetDebugMethods($DebugObject);
		$tempProperties = $this->ExtractDebugProperties($tempMethods);
		
		foreach ($tempProperties as $Key => $Value)
		{
			if (is_numeric($Value))
			{
				$returnValue .= "<li>" . $Key . ": " . $Value . "</li>";	
			}
			else 
			{
				if ($Value == "null" || $Value == "array()" || substr($Value, 0, 9) == "<fieldset>")
				{
					$returnValue .= "<li>" . $Key . ": " . $Value . "</li>";	
				}
				else 
				{
					$returnValue .= "<li>" . $Key . ": \"" . $Value . "\"</li>";	
				}
			}			
		}
		$returnValue .= "</ul></fieldset>";
		
		$returnValue .= "</fieldset>";
		
		return $returnValue;
	}
	
	protected function GetDebugProtectedData($DebugObject)
	{
		$tempProperties = $DebugObject->getProperties();
		
		foreach ($tempProperties as $Property)
		{
			$tempPropertyName = $Property->name;
			$tempPropertyValue = $this->$tempPropertyName;
			
			if (is_null($tempPropertyValue))
			{
				$returnValue[$tempPropertyName] = "null";
			}
			elseif ($tempPropertyValue === false)
			{
				$returnValue[$tempPropertyName] = "false";
			}
			elseif (is_object($tempPropertyValue))
			{
				$returnValue[$tempPropertyName] = $this->DebugObjectDisplay($tempPropertyValue);
			}
			elseif (is_array($tempPropertyValue))
			{
				$returnValue[$tempPropertyName] = $this->DebugArrayDisplay($tempPropertyValue); 
			}
			else
			{
				$returnValue[$tempPropertyName] = $tempPropertyValue;
			}
		}
		
		return $returnValue;
	}
	
	protected function DebugArrayDisplay($Array)
	{
		if (count($Array) > 0)
		{
			foreach ($Array as $key => $value)
			{
				$returnValue .= "<fieldset><legend>$key</legend>";
				if (is_array($value))
				{
					$this->DebugArrayDisplay($value);
				}
				elseif (is_object($value))
				{
					$returnValue .= $this->DebugObjectDisplay($value);
				}
				else
				{
					$returnValue .= $value;
				}
				$returnValue .= "</fieldset>";
			}
		}
		else
		{
			$returnValue .= "array()";
		}
		
		return $returnValue;
	}
	
	protected function DebugObjectDisplay($Object)
	{
		if ($Object instanceof Component)
		{
			$returnValue = $Object->__toString();
		}
		else
		{
			$returnValue = "This Object cannot be debugged!";
		}
		
		return $returnValue;
	}
	
	protected function GetDebugMethods($DebugObject)
	{
		$tempMethods = $DebugObject->getMethods();
		foreach ($tempMethods as $Method)
		{
			if ($Method->isPublic())
			{
				$tempMethodName = $Method->name;
				$returnValue[$tempMethodName] = $Method->getParameters();
			}
		}
			
		return $returnValue;
	}
	
	protected function ExtractDebugProperties($DebugMethods)
	{
		foreach ($DebugMethods as $Key => $Value)
		{
			if (strpos($Key, 'get') === 0)
			{
				try
				{
					$propertyValue = $this->$Key();
				}
				catch (exception $e)
				{
					$propertyValue = "Exception Thrown!";
				}
				
				$propertyName = substr($Key,3);
				
				if (is_null($propertyValue))
				{
					$returnValue[$propertyName] = "null";
				}
				elseif ($propertyValue === false)
				{
					$returnValue[$propertyName] = "false";
				}
				elseif (is_object($propertyValue))
				{
					$returnValue[$propertyName] = $this->DebugObjectDisplay($propertyValue); 
				}
				elseif (is_array($propertyValue))
				{
					$returnValue[$propertyName] = $this->DebugArrayDisplay($propertyValue); 
				}
				else
				{
					$returnValue[$propertyName] = $propertyValue;
				}
				
				$propertyValue = "";
				$propertyName = "";
			}
		}
		return $returnValue;
	}
}

?>