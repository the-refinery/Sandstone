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
		$Namespace = "codebase/" . $Namespace;

		require_once($Namespace);
	}

	static public function AutoLoad($ClassName)
	{

	}
}

function __autoload($ClassName)
{
	Namespace::AutoLoad($ClassName);
}
