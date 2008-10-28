<?php

NameSpace::Using("Sandstone.Application");

class JavascriptPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = false;
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Return in plain text
		header('Content-Type: text/plain');
		
		// Require the application file first, if it exists
		if (file_exists(Application::License()->AccountFileSpec . "javascript/" . $EventParameters['library'] . ".js"))
		{
			require_once(Application::License()->AccountFileSpec . "javascript/" . $EventParameters['library'] . ".js");	
		}
		else
		{
			require_once('javascript/' . $EventParameters['library'] . ".js");	
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>