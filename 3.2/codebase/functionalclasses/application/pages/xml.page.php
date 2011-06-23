<?php

SandstoneNamespace::Using("Sandstone.Application");

class XMLPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Return in plain text
		header('Content-Type: text/xml');
		
		if (file_exists(Application::License()->AccountFileSpec . "xml/" . $EventParameters['library'] . ".xml"))
		{
			require_once(Application::License()->AccountFileSpec . "xml/" . $EventParameters['library'] . ".xml");	
		}
		else
		{
			require_once('xml/' . $EventParameters['library'] . ".xml");	
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>