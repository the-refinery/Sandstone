<?php

// Include codebase
$currentPath = dirname(__FILE__);
include_once($currentPath . "/../include.php");

// Include the specs to run
include_once("testing/describesbehavior.spec.php");
include_once("testing/specrunner.spec.php");
include_once("testing/assertscondition.spec.php");
include_once("templateengine/templateengine.spec.php");

$SpecRunner = new ConsoleSpecRunner();
$SpecRunner->DescribeBehavior('DescribesBehaviorSpec');
$SpecRunner->DescribeBehavior('SpecRunnerSpec');
$SpecRunner->DescribeBehavior('AssertsConditionSpec');
$SpecRunner->DescribeBehavior('TemplateEngineSpec');
$SpecRunner->Run();

