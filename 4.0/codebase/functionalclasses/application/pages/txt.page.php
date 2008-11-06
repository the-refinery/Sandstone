<?php

NameSpace::Using("Sandstone.Application");

class TXTPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Return in plain text
		header('Content-Type: text/plain');
		
		if (file_exists(Application::License()->AccountFileSpec . "txt/" . $EventParameters['library'] . ".txt"))
		{
			require_once(Application::License()->AccountFileSpec . "txt/" . $EventParameters['library'] . ".txt");	
		}
		else
		{
			require_once('txt/' . $EventParameters['library'] . ".txt");	
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>