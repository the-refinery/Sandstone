<?php

SandstoneNamespace::Using("Sandstone.Application");

class ScriptaculousPage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected function JS_Processor($EventParameters)
	{

		//Load Prototype
		$libraryFileSpec = "javascript/prototype.js";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Now add effect
		$libraryFileSpec = "javascript/effects.js";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		echo $libraryContents;

	}

}

?>