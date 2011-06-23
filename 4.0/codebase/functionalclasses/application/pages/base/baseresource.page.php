<?php

SandstoneNamespace::Using("Sandstone.Application");

class BaseResourcePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected function JS_Processor($EventParameters)
	{
		//Load Prototype & Scriptaculous compressed
		$libraryFileSpec = "javascript/jquery.js";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Now add ajax functions
		$libraryFileSpec = "javascript/ajax.js";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Now add control functions
		$libraryFileSpec = "javascript/controls.js";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		echo $this->CompressJavascript($libraryContents);
	}

	protected function CSS_Processor($EventParameters)
	{
		//Load meyer reset
		$libraryFileSpec = "css/meyerreset.css";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Load jquery 
		$libraryFileSpec = "css/jquery.css";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Load jquery theme
		$libraryFileSpec = "css/jquery-theme.css";
		$libraryContents = file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);
		
		//Load controls
		$libraryFileSpec = "css/controls.css";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		//Load error
		$libraryFileSpec = "css/error.css";
		$libraryContents .= file_get_contents($libraryFileSpec, FILE_USE_INCLUDE_PATH);

		echo $this->CompressCSS($libraryContents);
	}
}

?>