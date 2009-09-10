<?php
/*
Show Stack Trace Exception

@package Sandstone
@subpackage Exception
*/

class ShowStackTraceException extends DIException
{

	public function __construct($Message, $Code = 0)
	{
		parent::__construct($Message, $Code);

		$this->_severity = self::INFO;

		//Since we know the top of this call stack will be the Application functions file,
		//which is meaningless, just remove it.
		unset($this->_diCallStack[0]);

	}

	public function __toString()
	{
		$returnValue .= '<h2>Stack Trace</h2>';

		$returnValue .= $this->DItraceAsString();

		return $returnValue;
	}


}

?>
