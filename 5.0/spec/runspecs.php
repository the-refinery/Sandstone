<?php

// Include codebase
$currentPath = dirname(__FILE__);
include($currentPath . "/../include.php");

// Include the specs to run
include("dispec/dispec.spec.php");
include("templateengine/templateengine.spec.php");

$SpecRunner = new ConsoleSpecRunner();
$SpecRunner->AddSpecSuite('DISpecSuiteSpec');
$SpecRunner->Run();

