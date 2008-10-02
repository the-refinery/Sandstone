<?php

NameSpace::Using("Sandstone.Application");

class RSSPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
		
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Return in plain text
		header('Content-Type: text/xml');
		
		if (file_exists(Application::License()->AccountFileSpec . "rss/" . $EventParameters['library'] . ".rss"))
		{
			require_once(Application::License()->AccountFileSpec . "rss/" . $EventParameters['library'] . ".rss");	
		}
		else
		{
			require_once('rss/' . $EventParameters['library'] . ".rss");	
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}

}

?>