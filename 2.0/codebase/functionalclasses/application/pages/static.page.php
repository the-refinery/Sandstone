<?php

SandstoneNamespace::Using("Sandstone.Application");

/* This page is used for displaying templates with no functionality, just static content */
class StaticPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = false;
	
	protected $_smartyTemplateName = "togetchanged.tpl";
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		$this->_smartyTemplateName = $EventParameters['template'];
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>