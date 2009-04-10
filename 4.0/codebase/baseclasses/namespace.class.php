<?php
/*
Namespace Class File
@package Sandstone
@subpackage BaseClasses
*/

class NameSpace extends Module
{

	protected $_includedFiles = Array();
	protected $_usedNameSpaces = Array();

	protected $_classNames = Array();
	protected $_pageNames = Array();
	protected $_controlNames= Array();

 	protected $_nameSpaceRootLocations;
 	protected $_nameSpaceEnvironmentBases;

 	protected $_templateSearchPath;

	protected $_classesByNamespace = Array();

	protected  function __construct()
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

	static public function SetEnvironment($NameSpaceEnvironments)
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->SetupNameSpaceEnvironments($NameSpaceEnvironments);

		return $returnValue;
	}

	static public function Using($NameSpace)
	{

		$ns = NameSpace::Instance();

		$returnValue = $ns->UseNameSpace($NameSpace);

		return $returnValue;

	}

	static public function AddFiles($FileList)
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->IncludeNameSpaceFiles($FileList);

		return $returnValue;
	}

	static public function AutoLoad($ClassName)
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->RequireClassFile($ClassName);

		return $returnValue;

	}

	static public function PageSpace($PageClassName)
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->GetPageSpace($PageClassName);

		return $returnValue;
	}

	static public function IsInUse($NameSpace)
	{

		$ns = NameSpace::Instance();

		$returnValue = $ns->GetIsInUse($NameSpace);

		return $returnValue;
	}

	static public function UseDynamicApplicationPages()
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->ProcessDynamicApplicationPages();

		return $returnValue;
	}

	static public function Display()
	{

		$ns = NameSpace::Instance();

		$ns->ShowDisplay();

	}

	static public function ClassNames()
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->GetClassNames();

		return $returnValue;
	}

	static public function PageNames()
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->GetPageNames();

		return $returnValue;
	}

	static public function ControlNames()
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->GetControlNames();

		return $returnValue;
	}

	static public function NamespaceEnviromentBase($NameSpace)
	{

		$ns = NameSpace::Instance();

		$returnValue = $ns->GetNamespaceEnviromentBase($NameSpace);

		return $returnValue;

	}

	static public function TemplateSearchPath()
	{
		$ns = NameSpace::Instance();

		$returnValue = $ns->GetTemplateSearchPath();

		return $returnValue;

	}

	protected function SetupNameSpaceEnvironments($NameSpaceEnvironments)
	{

		//First, load the root locations for all namespaces.
		$this->_nameSpaceRootLocations = $this->LoadNameSpaceRootLocations();

		//Make sure the Application namespace is set in the NameSpaceEnvironments array
		if (array_key_exists("application", $NameSpaceEnvironments) == false)
		{
			$NameSpaceEnvironments["application"] = "";
		}

		//Now add the environment directory from the passed array
		foreach ($this->_nameSpaceRootLocations as $tempNameSpace=>$tempDirectory)
		{
			if (array_key_exists($tempNameSpace, $NameSpaceEnvironments))
			{
				$environmentBase = $tempDirectory;

				if (strlen($NameSpaceEnvironments[$tempNameSpace]) > 0)
				{
					if (strtolower($NameSpaceEnvironments[$tempNameSpace]) != "trunk")
					{
						$environmentBase .= $NameSpaceEnvironments[$tempNameSpace] . "/";
					}
				}

				//Set the namespace root
				$this->_nameSpaceRootLocations[$tempNameSpace] = $environmentBase . "namespaces/";

				//Update the include path to allow us to find the class files
				set_include_path(get_include_path() . PATH_SEPARATOR . $environmentBase);

				//Save the environment base
				$this->_nameSpaceEnvironmentBases[$tempNameSpace] = $environmentBase;
			}
			else
			{
				//There isn't anything defined for this namespace, so we will
				//remove it
				unset($this->_nameSpaceRootLocations[$tempNameSpace]);
			}

		}

		//The development namespace always carries the same root location as the application
		$this->_nameSpaceRootLocations["development"] = $this->_nameSpaceRootLocations["application"];

		return true;
	}

	protected function LoadNameSpaceRootLocations()
	{
		GLOBAL $SANDSTONE_ROOT_LOCATION;
		GLOBAL $APPLICATION_ROOT_LOCATION;

		//Load the root locations from the main config file.
		$fileSpec = $SANDSTONE_ROOT_LOCATION . "namespacerootlocations.cfg";

		$keyValuePairs = file($fileSpec);

		foreach($keyValuePairs as $pairs)
		{
			$tempArray = explode(",", rtrim($pairs));

			if (substr($tempArray[1], strlen($tempArray[1]) - 1) != "/")
			{
				$tempArray[1] .= "/";
			}

			$returnValue[strtolower($tempArray[0])] = trim($tempArray[1]);
		}


		//Add the application root location
		$returnValue['application'] = $APPLICATION_ROOT_LOCATION;

		return $returnValue;

	}

	protected function UseNameSpace($NameSpace)
	{
		//Have we already used this namespace?
		if (key_exists(strtolower($NameSpace), $this->_usedNameSpaces) == false)
		{

			//First determine if this is a wildcard reference
			if (substr($NameSpace, -1, 1) == "*")
			{
				//Wildcard Namespace reference
				$returnValue = $this->ProcessWildcardNameSpace($NameSpace);
			}
			else
			{
				//Specific Namespace reference
				$returnValue = $this->ProcessSpecificNamespace($NameSpace);
			}
		}
		else
		{
			//Everything is ok, but we don't need to double process it.
			$returnValue = true;
		}

		return $returnValue;
	}

	protected function DetermineRootLocation($NameSpace)
	{
		$tree = explode(".", $NameSpace);
		$rootNameSpace = strtolower($tree[0]);

		$returnValue = $this->_nameSpaceRootLocations[$rootNameSpace];

		return $returnValue;
	}

	protected function ProcessWildcardNameSpace($NameSpace)
	{
		//Determine the file location of the root namespace.
		$rootLocation = $this->DetermineRootLocation($NameSpace);

		//Add the wildcard reference to our used list.
		$this->_usedNameSpaces[strtolower($NameSpace)] = true;

		//Strip the wildcard and attempt to load the "parent" namespace
		//if it's defined.  We don't care about the success of this, since
		//if it's not there, it's ok.
		$parentNameSpace = substr($NameSpace, 0, -2);
		$this->ProcessSpecificNamespace($parentNameSpace);

		//Are there any matching files?
		$fileList = glob($rootLocation . strtolower($NameSpace) . ".ns");

		if ($fileList && count($fileList) > 0)
		{

			//Default to success, and if we have a failure at some point in the
			//loop, then change this.
			$returnValue = true;

			foreach($fileList as $tempFileSpec)
			{
				//Determine the specific NameSpace for this file, and
				//add it to our used array
				$tempNameSpace = substr($tempFileSpec, strlen($rootLocation));
				$tempNameSpace = substr($tempNameSpace, 0, strlen($tempNameSpace) - 3);

				//Do we already have this namespace in use?
				if (key_exists(strtolower($tempNameSpace), $this->_usedNameSpaces) == false)
				{
					//We don't, so process it.
					$this->_usedNameSpaces[strtolower($tempNameSpace)] = true;

					//Now process this namespace
					$success = $this->IncludeNamespaceFileContents($tempFileSpec);

					if ($success == false)
					{
						$returnValue = false;
					}
				}
			}
		}
		else
		{
			//There weren't any matching files.
			$returnValue = false;
		}

		return $returnValue;

	}

	protected function ProcessSpecificNamespace($NameSpace)
	{
		//Determine the file location of the root namespace.
		$rootLocation = $this->DetermineRootLocation($NameSpace);

		//Build the actual filespec
		$nameSpaceFileSpec = $rootLocation .  strtolower($NameSpace) . ".ns";

		//Add it to our used list
		$this->_usedNameSpaces[strtolower($NameSpace)] = true;

		//Include its files.
		$returnValue = $this->IncludeNamespaceFileContents($nameSpaceFileSpec);

		return $returnValue;
	}

	protected function IncludeNamespaceFileContents($FileSpec)
	{
		//Does the file exist?
		if (file_exists($FileSpec))
		{
			//It does exist, get it's contents as an array file file specs.
			$targetFiles = ReadFileContents($FileSpec);

			$returnValue = $this->IncludeNameSpaceFiles($targetFiles);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;


	}

	public function IncludeNameSpaceFiles($TargetFiles)
	{
		//Which files do we need to include?
		$filesToInclude = array_diff($TargetFiles, $this->_includedFiles);

		//Add them to the master array, and automatically include the
		//non-class files.
		foreach($filesToInclude as $tempFile)
		{

			if (substr($tempFile, -9) == "class.php")
			{
				//Class files are loaded on demand, so add the file to the
				//array keyed by the class name.

				//Pick the actual class name out of the file name
				$classNameStart = strrpos($tempFile, "/") + 1;
				$classNameEnd = strlen($tempFile) - 10;
				$classNameLength = $classNameEnd - $classNameStart;
				$className = strtolower(substr($tempFile, $classNameStart, $classNameLength));

				//Just add it to the array, we'll include it when we need it.
				$this->_includedFiles[$className] = $tempFile;

				//Add it to our class names array
				$this->_classNames[] = $className;
			}
			else if(substr($tempFile, -8) == "page.php")
			{
				//Page files are loaded on demand, so just add the file
				//to the array keyed by the class name of the page (i.e. HomePage)

				//Build the actual class name from the file name
				$pageNameStart = strrpos($tempFile, "/") + 1;
				$pageNameEnd = strlen($tempFile) - 9;
				$pageNameLength = $pageNameEnd - $pageNameStart;
				$pageClassName = strtolower(substr($tempFile, $pageNameStart, $pageNameLength)) . "page";

				//Just add it to the array, we'll include it when we need it.
				$this->_includedFiles[$pageClassName] = $tempFile;

				//Add it to our page names array
				$this->_pageNames[] = $pageClassName;
			}
			else if(substr($tempFile, -11) == "control.php")
			{
				//Control files are loaded on demand, so just add the file
				//to the array keyed by the class name of the control (i.e. TextBoxControl)

				//Build the actual class name from the file name
				$controlNameStart = strrpos($tempFile, "/") + 1;
				$controlNameEnd = strlen($tempFile) - 12;
				$controlNameLength = $controlNameEnd - $controlNameStart;
				$controlClassName = strtolower(substr($tempFile, $controlNameStart, $controlNameLength)) . "control";

				//Just add it to the array, we'll include it when we need it.
				$this->_includedFiles[$controlClassName] = $tempFile;

				//Add it to our control names array
				$this->_controlNames[] = $controlClassName;
			}
			else
			{
				//This is something other than a class or page file,
				//so simply include it now.
				$this->_includedFiles[] = $tempFile;
				$this->RequireFile($tempFile);
			}
		}

		$returnValue = true;


		return $returnValue;

	}

	protected function RequireClassFile($ClassName)
	{

		//Force this to lower case to make sure we find the index
		$ClassName = strtolower($ClassName);

		if (key_exists($ClassName, $this->_includedFiles))
		{

			$this->RequireFile($this->_includedFiles[$ClassName]);

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	protected function RequireFile($FileSpec)
	{
		//This is just a wrapper for a normal require method call.
		//it's only here to allow for debugging of when files are acutally included.
		require($FileSpec);

	}

	protected function GetPageSpace($PageClassName)
	{

		$PageClassName = strtolower($PageClassName);

		if (substr($PageClassName, strlen($PageClassName) - 4, 4) == "page")
		{
			if (array_key_exists($PageClassName, $this->_includedFiles))
			{
				$fileSpec = $this->_includedFiles[$PageClassName];

				$fileNameStart = strrpos($fileSpec, "/");

				$returnValue = substr($fileSpec, 0, $fileNameStart+1);
			}
			else
			{
				$returnValue = null;
			}
		}
		else
		{
			$returnValue = null;
		}


		return $returnValue;
	}

	protected function GetIsInUse($NameSpace)
	{

		if (array_key_exists(strtolower($NameSpace), $this->_usedNameSpaces))
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function ProcessDynamicApplicationPages()
	{
		GLOBAL $APPLICATION_ROOT_LOCATION;

		//This dynamically builds a list of all available pages
		//and includes them
		$topPagesDirectory = $APPLICATION_ROOT_LOCATION . "pages/";

		$returnValue = $this->ProcessPageDirectory($topPagesDirectory);

		if ($returnValue == true)
		{
        	//Add the Application.Pages namespace to our used list
			$this->_usedNameSpaces["application.pages"] = true;
		}

		return $returnValue;
	}

	protected function ProcessPageDirectory($Directory)
	{
		GLOBAL $APPLICATION_ROOT_LOCATION;

		$returnValue = false;

		//Get a list of any sub directories
		$pattern = $Directory . "*";
		$subDirs = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);

		//Now recurse down into them
		foreach ($subDirs as $tempDir)
		{
			$returnValue = $this->ProcessPageDirectory($tempDir);
		}

		//Next, are there any page files in this directory?
		$pattern = $Directory . "*.page.php";
		$pages = glob($pattern);

		if ($pages && count($pages) > 0)
		{
			$appRootLen = strlen($APPLICATION_ROOT_LOCATION);

			//Trim the application root off each filespec
			//and convert any windows \ marks to /
			foreach($pages as $key => $value)
			{
				$pages[$key] = str_replace("\\", "/", substr($value, $appRootLen));
			}

			//Now include the files
			$this->IncludeNameSpaceFiles($pages);
			$returnValue = true;
		}

		return $returnValue;

	}

	protected function ShowDisplay()
	{

		$nsList = $this->_usedNameSpaces;

		ksort($nsList);

		echo "<h1>Namespaces</h1>";
		echo "<ul>";
		foreach ($nsList as $key=>$value)
		{
			if (strpos($key, "*") === false)
			{
				echo "<li>{$key}</li>";
			}
		}
		echo "</ul>";


		$fileList = $this->_includedFiles;

		ksort($fileList);

		echo "<h1>Classes</h1>";
		echo "<table>";
		echo "<tr><th>Class</th><th>File</th></tr>";

		foreach ($fileList as $key=>$value)
		{
			if (is_numeric($key))
			{
				$funcs[] = $value;
			}
			else
			{
				echo "<tr>";
				echo "<td>{$key}</td>";
				echo "<td>{$value}</td>";
				echo "</tr>";
			}
		}

		echo "</table>";

		echo "<h1>Function Files</h1>";
		echo "<ul>";
		foreach ($funcs as $value)
		{
			echo "<li>{$value}</li>";
		}
		echo "</ul>";

		echo "<h1>Namespace Environment Bases</h1>";
		echo "<table>";
		echo "<tr><th>Namespace</th><th>Environment Base</th></tr>";

		foreach ($this->_nameSpaceEnvironmentBases as $key=>$value)
		{
        		echo "<tr>";
				echo "<td>{$key}</td>";
				echo "<td>{$value}</td>";
				echo "</tr>";
		}

		echo "</table>";

	}

	protected function GetClassNames()
	{
		return $this->_classNames;
	}

	protected function GetPageNames()
	{
		return $this->_pageNames;
	}

	protected function GetControlNames()
	{
		return $this->_controlNames;
	}

	protected function GetNamespaceEnviromentBase($NameSpace)
	{

		$returnValue = $this->_nameSpaceEnvironmentBases[strtolower($NameSpace)];

		return $returnValue;
	}

	protected function GetTemplateSearchPath()
	{

		if (is_set($this->_templateSearchPath) == false)
		{
			foreach ($this->_nameSpaceEnvironmentBases as $tempNamespace=>$tempEnvBase)
			{
				$target = $tempEnvBase . "resources/templates/";

				$templateDirs = Template::FindDirectoriesWithTemplates($target);

				if (count($templateDirs) > 0)
				{
					switch ($tempNamespace)
					{
						case "application":
							$applicationTemplates = implode(PATH_SEPARATOR, $templateDirs);
							break;

						case "sandstone":
							$sandstoneTemplates = implode(PATH_SEPARATOR, $templateDirs);
							break;

						default:
							if (strlen($namespaceTemplates) > 0)
							{
								$namespaceTemplates .= PATH_SEPARATOR;
							}
							
							$namespaceTemplates .= implode(PATH_SEPARATOR, $templateDirs);
							break;
					}
				}
			}

			//We will search the application first
			$this->_templateSearchPath = $applicationTemplates;

			//Then anything in the general namespaces
			if (strlen($namespaceTemplates) > 0)
			{
				if (strlen($this->_templateSearchPath) > 0)
				{
					$this->_templateSearchPath .= PATH_SEPARATOR;
				}

				$this->_templateSearchPath .= $namespaceTemplates;
			}

			//Finally we will look to Sandstone
			if (strlen($sandstoneTemplates) > 0)
			{
				if (strlen($this->_templateSearchPath) > 0)
				{
					$this->_templateSearchPath .= PATH_SEPARATOR;
				}

				$this->_templateSearchPath .= $sandstoneTemplates;
			}

		}

		return $this->_templateSearchPath;
	}
}

?>