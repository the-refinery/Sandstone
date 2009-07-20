<?php

class RouteSpec extends DescribeBehavior
{
	public function ItShouldCreateARouteByUrlDescription()
	{
		$foo = new Route(array("home"));

		return $this->Expects($foo->Parameters)->ToContain("home");
	}

	public function ItShouldAcceptMultipleParameters()
	{
		$foo = new Route(array("home", "foo", "bar"));

		return $this->Expects(count($foo->Parameters))->ToBeEqualTo(3);
	}

	public function ItShouldReportTheDefinedPath()
	{
		$foo = new Route(array("foo", ":fooid"));

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/:fooid');
	}
}
