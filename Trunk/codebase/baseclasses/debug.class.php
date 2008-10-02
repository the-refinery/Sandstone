<?php
/*
Debug Class File

@package Sandstone
@subpackage BaseClasses
*/

class Debug extends Component
{
	/*
	Show a blackbox view of the current object
	*/
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

    public function __toString()
    {

        $divColor = "#ddd";
        $liColor = "#ffc";
        $liBorder = "#fcc";

        $randomID = rand();
        $className = get_class($this);
        $anchorID = "{$className}_{$randomID}";

        $detailJS = "    document.getElementById('{$anchorID}_summary').style.display = 'none';
                        document.getElementById('{$anchorID}_detail').style.display = 'block';";

        $summaryJS = "    document.getElementById('{$anchorID}_detail').style.display = 'none';
                        document.getElementById('{$anchorID}_summary').style.display = 'block';";


        $returnValue = "<a id=\"{$anchorID}\"></a>";

        //Summary DIV
        $returnValue .= "<div id=\"{$anchorID}_summary\" style=\"border: 0; background-color: {$divColor}; padding: 6px;\">";
        $returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$detailJS}\"><b>{$className}</b></a>";
        $returnValue .= "</div>";

        //Detail DIV
        $returnValue .= "<div id=\"{$anchorID}_detail\" style=\"border: 0; background-color: {$divColor}; padding: 6px; display:none;\">";
        $returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">{$className}</h1>";

        $returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

        $DebugObject = new ReflectionClass(get_class($this));

        foreach ($this->GetDebugProtectedData($DebugObject) as $key => $value)
        {
            $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";

            $returnValue .= "<strong>{$key}: </strong> ";

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
                if (is_numeric($value) || $value == "null")
                {
                    $returnValue .= "{$value}";
                }
                else
                {
                    $returnValue .= "\"{$value}\"";
                }
            }

            $returnValue .= "</li>";
        }

        $returnValue .= "</ul>";

        $returnValue .= "<a href=\"javascript:void(0);\" onClick=\"{$summaryJS}\">Close</a>";

        $returnValue .= "</div>";

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
                $returnValue[$tempPropertyName] = $tempPropertyValue;
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
		if ($Object instanceof Component || $Object instanceof DIarray)
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