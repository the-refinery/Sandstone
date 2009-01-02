<?php

NameSpace::Using("Sandstone.Application");

class HomePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected function Load_Handler($EventParameters)
	{

		$returnValue = new EventResults();

		echo "<h1>Home Page</h1>";
		echo "<h2>Desigining Interactive's Sandstone Foundation successfully installed.<h2>";
		echo "<h3>Currently Available Pages</h3>";

		$pageList = NameSpace::PageNames();
		asort($pageList);

		$appPagesOutput = "<h4>Application Pages</h4><ul>";
		$sandstonePagesOutput = "<h4>Sandstone Pages</h4><ul>";

		foreach($pageList as $tempPage)
		{
			$pageName = strtolower(substr($tempPage, 0, strlen($tempPage) - 4));
			$pageDisplay = ucfirst($pageName);

			if ($pageName != "home")
			{
				$pageSpace = NameSpace::PageSpace($tempPage);

				if (substr($pageSpace, 0, 5) == pages)
				{
					//Application Page
					$appPagesOutput .= "<li><a href='/{$pageName}'>{$pageDisplay}</a> <i>({$pageSpace})</i></li>";
				}
				else
				{
					//Sandstone Page
					$sandstonePagesOutput .= "<li><a href='/{$pageName}'>{$pageDisplay}</a> <i>({$pageSpace})</i></li>";
				}
			}
		}
		$appPagesOutput .= "</ul>";
		$sandstonePagesOutput .= "</ul>";

		echo $appPagesOutput;
		echo $sandstonePagesOutput;

		$returnValue->Value = true;
		$returnValue->Complete();

		return $returnValue;
	}

}

?>