<?php
/*
Page 404 Exception Class

@package Sandstone
@subpackage Exception
*/

class Page404exception extends DIException 
{
	
	protected $_pageName;
	protected $_seoPageName;
	
	public function __construct($Message, $PageName, $SEOpageName = null, $Code = 0)
	{		
		parent::__construct($Message, $Code);
		
		$this->_pageName = $PageName;
		$this->_seoPageName = $SEOpageName;
	}

	public function __toString()
	{
		$returnValue .= '<h2>' . $this->getMessage() . '</h2>';
		
		if (is_set($this->_seoPageName))
		{
			$returnValue .= '<h3><b>SEO Page:</b> ' . $this->_seoPageName . '</h3>';	
		}
		else 
		{
			$returnValue .= '<h3><b>Page:</b> ' . $this->_pageName . '</h3>';	
		}

		$returnValue .= $this->DItraceAsString();
				
		return $returnValue;
	}
	
	
}

?>