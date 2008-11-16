<?php

NameSpace::Using("Sandstone.Application");

class ScriptaculousPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected function JS_Processor($EventParameters)
	{

		//Load Prototype & Scriptaculous compressed
		$libraryFileSpec = "javascript/protoaculous.js";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Now add application js
		$libraryFileSpec = "javascript/application.js";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		echo $libraryContents;

	}

}

?>