<?php

class Namespace
{
	protected $_includedClasses;
	protected $_usedNamespaces;

	protected function __construct()
	{
		$this->_includedClasses = Array();
		$this->_usedNamespaces = Array();
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
		$ns = Namespace::Instance();

		$ns->ProcessUsing($Namespace);
	}

	static public function AutoLoad($ClassName)
	{
		$ns = Namespace::Instance();

		$ns->ProcessAutoLoad($ClassName);
	}

	public function ProcessUsing($Namespace)
	{
		if (array_key_exists(strtolower($Namespace), $this->_usedNamespaces) == false)
		{
			$namespaceDirectory = $this->GenerateBasePathFromNamespace($Namespace);

			$parser = new ParseADirectory();

			$files = $parser->FindFilesInADirectory($namespaceDirectory . "*.*.php");

			foreach ($files as $tempFile)
			{
				$this->ProcessFile($tempFile);
			}

			$this->_usedNamespaces[$Namespace] = $namespaceDirectory;
		}

	}

	protected function GenerateBasePathFromNamespace($Namespace)
	{
		
		$returnValue = strtolower($Namespace);

		$returnValue = str_replace(".", "/", $returnValue);

		$returnValue = "codebase/" . $returnValue . "/";

		return $returnValue;
	}

	protected function ProcessFile($FileSpec)
	{
		$className = $this->ParseClassName($FileSpec);

		$this->_includedClasses[$className] = $FileSpec;
	}

	protected function ParseClassName($FileSpec)
	{
		$fileNameStart = strrpos($FileSpec, "/") + 1;
		$fileName = substr($FileSpec, $fileNameStart);

		$classNameEnd = strpos($fileName, ".");
		$returnValue = strtolower(substr($fileName, 0, $classNameEnd));

		return $returnValue;
	}

	public function ProcessAutoLoad($ClassName)
	{
		$ClassName = strtolower($ClassName);

		if (array_key_exists($ClassName, $this->_includedClasses))
		{
			require_once($this->_includedClasses[$ClassName]);
		}
	}
}

function __autoload($ClassName)
{
	Namespace::AutoLoad($ClassName);
}
