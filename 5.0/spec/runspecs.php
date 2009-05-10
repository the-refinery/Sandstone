<?php

// Include codebase
$currentPath = dirname(__FILE__);
include_once($currentPath . "/../include.php");

// Include the specs to run
include_once("dispec/describesbehavior.spec.php");
include_once("dispec/specrunner.spec.php");
include_once("dispec/assertscondition.spec.php");
include_once("templateengine/templateengine.spec.php");

$SpecRunner = new ConsoleSpecRunner();
$SpecRunner->AddSpecSuite('DescribesBehaviorSpec');
$SpecRunner->AddSpecSuite('SpecRunnerSpec');
$SpecRunner->AddSpecSuite('AssertsConditionSpec');
$SpecRunner->AddSpecSuite('TemplateEngineSpec');
$SpecRunner->Run();

