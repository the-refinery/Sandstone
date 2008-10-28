<?php
/**
 * Component Class File
 * 
 * @package Sandstone
 * @subpackage BaseClasses
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

/**
 * This abstract class provides standard routines necessary for our child classes.
 * This includes the .NET propery simulation using getProperty and setProperty to 
 * allow direct access to properties using the <code>$this->Property</code> syntax.
 *
 */
class Component
{
	/**
	 * Wrapper for property get routines
	 * 
	 * Allows for the retrieval of properties within a class by 
	 * a .NET similar syntax.  Prefix the property name with "get"
	 * in the method, and simply return the expected value.
	 * 
	 * The properties can be accessed from outside the class using <code>$this->Property</code>,
	 * rather than <code>$this->getProperty()</code>
	 *
	 * @param string $name Property Name
	 * @return the property method requested
	 */
	public function __get($Name)
	{
		$getter='get'.$Name;
		
		if(method_exists($this,$getter))
		{
			$returnValue = $this->$getter();
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}
		
		return $returnValue;
	}

	/**
	 * Wrapper for property set routines
	 *
	 * Allows for the setting of properties within a class by 
	 * a .NET similar syntax.  Prefix the property name with "set"
	 * in the method, with the value as the method attribute.
	 * 
	 * The properties can be set from outside the class using <code>$this->Property = 'abc';</code>,
	 * rather than <code>$this->setProperty('abc')</code>
	 * 
	 * @param string $name Property Name
	 * @param variant $value
	 */
	public function __set($Name, $Value)
	{
		$setter='set'.$Name;
		
		if(method_exists($this,$setter))
		{
			$this->$setter($Value);
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
	
	public function __call($Name, $Parameters)
	{
		//This will only fire if an undefined method is called.
		throw new InvalidMethodException("No Public Method: $Name()", get_class($this), $Name);
	}
	
	/**
	 * Checks for the existance of the requested property.
	 *
	 * @param string $name Property name
	 * @return boolean Pass or Fail
	 */
	public function hasProperty($Name)
	{
		return method_exists($this,'get'.$Name) || method_exists($this,'set'.$Name);
	}

}

?>