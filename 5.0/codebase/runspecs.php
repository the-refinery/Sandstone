<?php

include_once('setupspecs.php');

$SpecRunner = new RunSpecsInBash();
$SpecRunner->DescribeBehavior('FormatStringSpec');
$SpecRunner->DescribeBehavior('OutputToBashSpec');
$SpecRunner->DescribeBehavior('DescribeBehaviorSpec');
$SpecRunner->DescribeBehavior('RunSpecsSpec');
$SpecRunner->DescribeBehavior('AssertConditionSpec');
$SpecRunner->DescribeBehavior('MockSpec');
$SpecRunner->DescribeBehavior('AlterClassSpec');
$SpecRunner->DescribeBehavior('componentspec');
$SpecRunner->DescribeBehavior('NamespaceSpec');
$SpecRunner->DescribeBehavior('ParseADirectorySpec');
$SpecRunner->DescribeBehavior('BenchmarkSpec');
$SpecRunner->DescribeBehavior('RouteSpec');
$SpecRunner->DescribeBehavior('RoutingSpec');
$SpecRunner->Run();
