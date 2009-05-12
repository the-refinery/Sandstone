<?php

// Include codebase
$currentPath = dirname(__FILE__);
include_once($currentPath . "/../include.php");

// Include the specs to run
include_once("core/string/spec/formatstring.spec.php");
include_once("core/console/spec/outputtobash.spec.php");
include_once("lib/spec/spec/describebehavior.spec.php");
include_once("lib/spec/spec/runspecs.spec.php");
include_once("lib/spec/spec/assertcondition.spec.php");
include_once("lib/alterclass/spec/alterclass.spec.php");

$SpecRunner = new RunSpecsInBash();
$SpecRunner->DescribeBehavior('FormatStringSpec');
$SpecRunner->DescribeBehavior('OutputToBashSpec');
$SpecRunner->DescribeBehavior('DescribeBehaviorSpec');
$SpecRunner->DescribeBehavior('RunSpecsSpec');
$SpecRunner->DescribeBehavior('AssertConditionSpec');
$SpecRunner->DescribeBehavior('AlterClassSpec');
$SpecRunner->Run();

