<?php

NameSpace::Using("Sandstone.Application");

class JavascriptPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected function JS_Processor($EventParameters)
	{

		$libraryFileSpec = "javascript/{$EventParameters['library']}.js";

		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);			

		echo $libraryContents;

	}

}

?>