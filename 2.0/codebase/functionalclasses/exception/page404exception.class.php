<?php
/**
 * Page 404 Exception Class
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
		
		if (is_set($this->_seoPageName))
		{
			$returnValue .= '<h2><b>SEO Page:</b> ' . $this->_seoPageName . '</h2>';	
		}
		else 
		{
			$returnValue .= '<h2><b>Page:</b> ' . $this->_pageName . '</h2>';	
		}
		
		$returnValue .= '<h3>' . $this->getMessage() . '</h3>';

		$returnValue .= $this->DItraceAsString();
				
		return $returnValue;
	}
	
	
}

?>