<?php

class Namespace
{
	protected function __construct()
	{

	}

	static public function Instance()
	{
		static $nameSpace;

		if (isset($nameSpace) == false)
		{
			$nameSpace = new NameSpace();
		}

		return $nameSpace;
	}

	static public function Using($Namespace)
	{
		$BasePath = "codebase/" . $Namespace;

		$files = glob($BasePath . "*.*.php");

		foreach ($files as $tempFile)
		{
			require_once($tempFile);
		}
	}

	static public function AutoLoad($ClassName)
	{

	}
}

function __autoload($ClassName)
{
	Namespace::AutoLoad($ClassName);
}
