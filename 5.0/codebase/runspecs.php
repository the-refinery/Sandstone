<?php

include_once('setupspecs.php');

$SpecRunner = new RunSpecsInBash();

$classes = Namespace::Classes();
foreach ($classes as $tempClass)
{
	if (strlen($tempClass) - stripos($tempClass,'spec') == 4)
	{
		$SpecRunner->DescribeBehavior($tempClass);
	}
}

$SpecRunner->Run();
