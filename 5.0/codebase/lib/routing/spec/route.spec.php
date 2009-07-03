<?php

class RouteSpec extends DescribeBehavior
{
	public function ItShouldCreateARouteByUrlDescription()
	{
		$foo = new Route("Home");

		return $this->Expects($foo->Parameters)->ToContain("home");
	}

	public function ItShouldAcceptMultipleParameters()
	{
		$foo = new Route("Home/Foo/Bar");

		return $this->Expects(count($foo->Parameters))->ToBeEqualTo(3);
	}

	public function ItShouldReportTheDefinedPath()
	{
		$foo = new Route("Foo/:FooID");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/:fooid');
	}
}
