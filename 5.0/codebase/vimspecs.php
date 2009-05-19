<?php

include_once('setupspecs.php');

$SpecRunner = new RunSpecsAsMake();
$SpecRunner->DescribeBehavior('FormatStringSpec');
$SpecRunner->DescribeBehavior('OutputToBashSpec');
$SpecRunner->DescribeBehavior('DescribeBehaviorSpec');
$SpecRunner->DescribeBehavior('RunSpecsSpec');
$SpecRunner->DescribeBehavior('AssertConditionSpec');
$SpecRunner->DescribeBehavior('DingusSpec');
$SpecRunner->DescribeBehavior('AlterClassSpec');
$SpecRunner->DescribeBehavior('componentspec');
$SpecRunner->DescribeBehavior('NamespaceSpec');
$SpecRunner->DescribeBehavior('ParseADirectorySpec');
$SpecRunner->Run();

