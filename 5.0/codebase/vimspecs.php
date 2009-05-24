<?php

include_once('setupspecs.php');

$currentPath = dirname($argv[1]);

$parser = new ParseADirectory();
$moduleSpecFiles = $parser->FindFilesInADirectory($currentPath . "/spec/*.spec.php");
$localSpecFiles = $parser->FindFilesInADirectory($currentPath . "/*.spec.php");

$allSpecFiles = array_merge($moduleSpecFiles, $localSpecFiles);

$SpecRunner = new RunSpecsAsMake();
foreach ($allSpecFiles as $tempFile)
{
	$specName = basename($tempFile);
	$specName = substr($specName, 0, strpos($specName, '.'));
	$specName .= 'spec';
	
	$SpecRunner->DescribeBehavior($specName);
}
$SpecRunner->Run();

