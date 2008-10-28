<?php
/**
 * Page 403 Exception Class
 * 
 * @package Sandstone
 * @subpackage Exception
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
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
					<h2><b>Page: </b>' . $this->_pageName . '/' . $this->_eventName . '</h2>
					<h3>' . $this->getMessage() . '</h3>
		';

		$returnValue .= $this->DItraceAsString();
		
		return $returnValue;
	}
	
	
}

?>