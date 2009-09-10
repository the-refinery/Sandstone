<?php
/*
Invalid Property Exception Class

@package Sandstone
@subpackage Exception
*/

class InvalidMethodException extends DIException 
{
	
	protected $_className;
	protected $_methodName;
	
	public function __construct($Message, $ClassName, $MethodName, $Code = 0)
	{		
		parent::__construct($Message, $Code);
		
		$this->_className = $ClassName;
		$this->_methodName = $MethodName;		
		
		//Since we know the top of this call stack will be the component class,
		//and then the bad method call, both of which are meaningless, we'll just 
		//remove them.
		unset($this->_diCallStack[0]);
		unset($this->_diCallStack[1]);
	}

	public function __toString()
	{
				
		$returnValue .= 
		'
					<h2>' . $this->getMessage() . '</h2>
					<h3><b>Method: </b>' . $this->_className . '->' . $this->_methodName . '</h3>
		';
		
		$returnValue .= $this->DItraceAsString();
				
		return $returnValue;
	}
	
	
}

?>