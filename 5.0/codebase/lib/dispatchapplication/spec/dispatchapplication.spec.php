<?php

class DispatchApplicationSpec extends DescribeBehavior
{
	public function ItShouldDetermineTheRoute()
	{
		$mockRequest = array('routing' => 'foo/bar/3');
		$foo = new DispatchApplication($mockRequest);

		return $this->Expects($foo->Route)->ToBeEqualTo('foo/bar/3');
	}
}
