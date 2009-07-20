<?php

class Namespace extends BaseSingleton
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

	static public function Display()
	{
		$ns = Namespace::Instance();

		$ns->ProcessDisplay();
	}

	static public function Classes()
	{
		$ns = Namespace::Instance();

		return $ns->Classes;
	}

	public function getClasses()
	{
		return array_keys($this->_includedClasses);
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

		$returnValue = $this->DetermineApplicationRoot() . "codebase/" . $returnValue . "/";

		return $returnValue;
	}

	protected function DetermineApplicationRoot()
	{
		$path = dirname(__FILE__);
		$path =  substr($path, 0, strpos($path, 'codebase'));

		return $path;
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

		$fileNameParts = explode(".", strtolower($fileName));

		if ($fileNameParts[1] == "spec")
		{
			$fileNameParts[0] .= "spec";
		}

		$returnValue = $fileNameParts[0];

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

	public function ProcessDisplay()
	{
		var_dump($this->_usedNamespaces);
		var_dump($this->_includedClasses);
	}
}

function __autoload($ClassName)
{
	Namespace::AutoLoad($ClassName);
}
