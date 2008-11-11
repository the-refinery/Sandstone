<?php
/*
Collective Base Class File

@package Sandstone
@subpackage EntityBase
*/

NameSpace::Using("Sandstone.Database");

class CollectiveBase extends Module
{

	protected $_name;

	protected $_parentEntity;

	protected $_elementType;
	protected $_elements;

	protected $_registeredProperties;
	protected $_registeredMethods;

	public function __construct($Name = null, $ParentEntity = null)
	{

		$this->_elements = new DIarray();

		$this->_name = $Name;

		if ($ParentEntity instanceof EntityBase)
		{
			$this->_parentEntity = $ParentEntity;
		}

	}

    public function Destroy()
    {
        if (is_set($this->_parentEntity))
        {
            $this->_parentEntity = null;
        }

        $this->_elements->Destroy();
    }

	public function __toString()
	{
		$className = get_class($this);

		$returnValue = "<div style=\"border: 0; background-color: #ddd; padding: 6px;\">";
		$returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">{$this->_name} ({$className} Collective)</h1>";

		$returnValue .= "<h2 style=\"padding: 0; margin: 10px 0 0 0;\">Parent Entity</h2>";

		if (is_set($this->_parentEntity))
		{
			$returnValue .=  $this->_parentEntity->__toString();
		}
		else
		{
			$returnValue .= "<em>not set</em>";
		}

		$returnValue .= "<h2 style=\"padding: 0; margin: 10px 0 0 0;\">Elements</h2>";

		$returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

		foreach ($this->_elements as $key=>$value)
		{
			$returnValue .= "<li style=\"border: 1px solid #fcc; margin: 2px; padding: 4px; background-color: #ffc;\">";
			$returnValue .= "[{$key}]" . $value->__toString();
			$returnValue .= "</li>";
		}

		$returnValue .= "</ul>";

		$returnValue .= "</div>";

		return $returnValue;
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

	public function getElements()
	{
		return $this->_elements;
	}

	public function Load()
	{
		return false;
	}

	public function AddElement($NewElement)
	{
		if ($NewElement instanceof $this->_elementType && $NewElement->IsLoaded)
		{

			if ($this->_isLoaded == false)
			{
				$this->Load();
			}

			if (array_key_exists($NewElement->PrimaryID, $this->_elements) == false)
			{
				$returnValue = $this->ProcessNewElement($NewElement);

				if ($returnValue == true)
				{
					$this->Load();
				}
			}
			else
			{
				$returnValue = true;
			}

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function ProcessNewElement($NewElement)
	{
		//This should be overriden in each child class.
		return false;
	}

	public function RemoveElement($OldElement)
	{
		if ($OldElement instanceof $this->_elementType && $OldElement->IsLoaded)
		{

			if ($this->_isLoaded == false)
			{
				$this->Load();
			}

			if (array_key_exists($OldElement->PrimaryID, $this->_elements))
			{

				$returnValue = $this->ProcessOldElement($this->_elements[$OldElement->PrimaryID]);

				if ($returnValue == true)
				{
					$this->Load();
				}
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

	protected function ProcessOldElement($OldElement)
	{
		//This should be overriden in each child class.
		return false;
	}

	public function ClearElements()
	{

		$returnValue = $this->ProcessClearElements();

		if ($returnValue == true)
		{
			$this->_elements->Clear();
		}

		return $returnValue;
	}

	protected function ProcessClearElements()
	{
		//This should be overriden in each child class.
		return false;
	}

	protected function SaveElements()
	{

		$returnValue = true;

		foreach($this->_elements as $tempElement)
		{
			$success = $this->ProcessSaveElement($tempElement);

			if ($success == false)
			{
				$returnValue = false;
			}
		}

		//Reload the elements
		if ($returnValue == true)
		{
			$this->Load();
		}

		return $returnValue;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		//This should be overriden in each child class.
		return false;
	}

	final public function Register($Properties, $Methods)
	{

		if (count($this->_registeredProperties) > 0)
		{
			foreach ($this->_registeredProperties as $tempProperty)
			{
				$Properties[strtolower($tempProperty)] = $this;
			}
		}

		if (count($this->_registeredMethods) > 0)
		{
			foreach ($this->_registeredMethods as $tempMethod)
			{
				$Methods[strtolower($tempMethod)] = $this;
			}
		}
	}

    public function Export()
	{

		foreach($this->_elements as $tempElement)
		{
			$this->_exportEntities[] = $tempElement->Export();
		}

		return parent::Export();

	}

}
?>