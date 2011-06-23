<?php

$accountBase = substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - 21);

if (file_exists("{$accountBase}accountsettings.inc.php"))
{
	require("{$accountBase}accountsettings.inc.php");
}

//What is this application's root location?
$applicationName = trim(strtolower(file_get_contents("application.name")));

$fileSpec = $SANDSTONE_ROOT_LOCATION . "applicationrootlocations.cfg";

$keyValuePairs = file($fileSpec);

$i = 0;
$isFound = false;

//Find the entry for our current application name
while ($i < count($keyValuePairs) && $isFound == false)
{
	$tempArray = explode(",", rtrim($keyValuePairs[$i]));

	if (strtolower($tempArray[0]) == $applicationName)
	{
		$isFound = true;

		$APPLICATION_ROOT_LOCATION = trim($tempArray[1]);

		//Make sure it ends with a slash
		if (substr($APPLICATION_ROOT_LOCATION, strlen($APPLICATION_ROOT_LOCATION) - 1) != "/")
		{
			$APPLICATION_ROOT_LOCATION .= "/";
		}
	}

	$i++;
}

//Load the application config file.  Data is "application namespace, environment"
//We'll load this into an array of Namespace=>Environment
$keyValuePairs = file("application.cfg");

foreach($keyValuePairs as $pairs)
{
	$tempArray = explode(",", rtrim($pairs));

	$nameSpaceEnvironments[strtolower($tempArray[0])] = ucfirst(strtolower(trim($tempArray[1])));
}

//Build the Sandstone path, and add it and the application root to the PHP include path, we'll load
$sandstonePath = $SANDSTONE_ROOT_LOCATION . $nameSpaceEnvironments['sandstone'] . "/";
$modulesPath  = $SANDSTONE_ROOT_LOCATION . "modules/";

set_include_path(get_include_path() . PATH_SEPARATOR . $APPLICATION_ROOT_LOCATION . PATH_SEPARATOR . $sandstonePath . PATH_SEPARATOR . $modulesPath);

//Now require the sandstone config file
require("sandstone.inc.php");

//Set the environment for the namespaces
SandstoneNamespace::SetEnvironment($nameSpaceEnvironments);

//Now that we have the sandstone base classes included, we can begin using Namespaces
//for everything else.  We'll start by using the Sandstone Application namespaces
//and the current application namespaces.
SandstoneNamespace::Using("Sandstone.Application.*");
SandstoneNamespace::Using("Sandstone.Routing");

SandstoneNamespace::Using("Application.*");

//If Application.Page isn't in use yet, build it dynamically
if (SandstoneNamespace::IsInUse("Application.Pages") == false)
{
	SandstoneNamespace::UseDynamicApplicationPages();
}

//Load the EntityBase classes to support the actual data entity objects
SandstoneNamespace::Using("Sandstone.Entity");

//Next, pull in any development namespaces that may exist (for local box development)
SandstoneNamespace::Using("Development.*");

//If this application has any default namespaces it should use,
//include them here
require("autonamespaces.php");

?>
