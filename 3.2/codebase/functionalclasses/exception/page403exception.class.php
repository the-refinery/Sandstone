<?php
/*
Page 403 Exception Class

@package Sandstone
@subpackage Exception
*/

class Page403exception extends DIException 
{
	
	protected $_pageName;
	protected $_eventName;
	
	public function __construct($Message, $PageName, $EventName, $Code = 0)
	{		
		parent::__construct($Message, $Code);
		
		$this->_pageName = $PageName;
		$this->_eventName = $EventName;
	}

	public function __toString()
	{
				
		$returnValue .= 
		'
					<h2>' . $this->getMessage() . '</h2>
					<h3><b>Page: </b>' . $this->_pageName . '/' . $this->_eventName . '</h3>
		';

		$returnValue .= $this->DItraceAsString();
		
		return $returnValue;
	}
	
	
}

?>