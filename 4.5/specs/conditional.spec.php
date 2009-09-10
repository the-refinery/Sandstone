<?php

Namespace::Using("Sandstone.Utilities.Conditional");

class ConditionalSpec extends SpecBase
{
	public function ItShouldCheckAValueIsBetweenTwoValues()
	{
		Check(IsInRange(5,3,10))->ShouldBeTrue();
	}

	public function ItShouldCheckAValueIsNotBetweenTwoValues()
	{
		Check(IsInRange(15,3,10))->ShouldNotBeTrue();
	}

}

?>
