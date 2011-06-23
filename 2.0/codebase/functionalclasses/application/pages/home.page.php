<?php

SandstoneNamespace::Using("Sandstone.Application");

class HomePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = false;

	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		echo "<h1>Home Page</h1>";
		echo "<h2>Desigining Interactive's Sandstone Foundation successfully installed.<h2>";
		echo "<h3>Currently Available Pages</h3>";
		
		$fileList = glob("pages/*.page.php");
		
		foreach($fileList as $tempFileName)
		{
			$pageNameStart = strrpos($tempFileName, "/") + 1;
			$pageNameEnd = strlen($tempFileName) - 9;
			$pageNameLength = $pageNameEnd - $pageNameStart;
			$pageName = strtolower(substr($tempFileName, $pageNameStart, $pageNameLength));
			$pageDisplay = ucfirst($pageName);

			if ($pageName != "home")
			{
				echo "<a href='/{$pageName}'>{$pageDisplay}</a><br>";		
			}
			
		}
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
}

?>