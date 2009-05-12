<?php

include_once('dependencies.php');

class AlterClassSpec extends DescribeBehavior
{
	public function ItShouldMixClasses()
	{
		AlterClass::Mixin('Target', 'Source');

		$target = new Target;
		
		return $this->Expects($target->Foo())->ToBeEqualTo('foo');
	}
}
