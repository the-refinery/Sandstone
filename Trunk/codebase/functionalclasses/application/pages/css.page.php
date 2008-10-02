<?php

NameSpace::Using("Sandstone.Application");

class CSSPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Return in plain text
		header('Content-Type: text/plain');
		
		if (file_exists(Application::License()->AccountFileSpec . "css/" . $EventParameters['library'] . ".css"))
		{
			require_once(Application::License()->AccountFileSpec . "css/" . $EventParameters['library'] . ".css");	
		}
		else
		{
			require_once('css/' . $EventParameters['library'] . ".css");	
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>