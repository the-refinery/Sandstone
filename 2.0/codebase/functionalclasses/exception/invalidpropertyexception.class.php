<?php
/**
 * Invalid Property Exception Class
 * 
 * @package Sandstone
 * @subpackage Exception
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class InvalidPropertyException extends DIException 
{
	
	protected $_className;
	protected $_propertyName;
	
	public function __construct($Message, $ClassName, $PropertyName, $Code = 0)
	{		
		parent::__construct($Message, $Code);
		
		$this->_className = $ClassName;
		$this->_propertyName = $PropertyName;		
		
		//Since we know the top of this call stack will be the component class,
		//which is meaningless, just remove it.
		unset($this->_diCallStack[0]);
	}

	public function __toString()
	{
		$returnValue .= 
		'
					<h2><b>Property: </b>' . $this->_className . '->' . $this->_propertyName . '</h2>
					<h3>' . $this->getMessage() . '</h3>
		';
		
		$returnValue .= $this->DItraceAsString();
		
		return $returnValue;
	}
	
	
}

?>