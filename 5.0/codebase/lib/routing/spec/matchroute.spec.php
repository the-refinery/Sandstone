<?php

class MatchRouteSpec extends DescribeBehavior
{
	public function ItShouldMatchAStaticRoute()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", "bar");

		$check = MatchRoute::CheckMatch($route, "Foo/BAR");

		return $this->Expects($check)->ToBeTrue();
	}

	public function ItShouldMatchADynamicRoute()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":fooid", "bar", ":barid");

		$check = MatchRoute::CheckMatch($route, "Foo/5/bar/3");

		return $this->Expects($check)->ToBeTrue();
	}

	public function ItShouldFindTheValueOfAKey()
	{
		return $this->Pending();
		$route = new Mock('Route');
		$route->Parameters = array("foo", "bar");

		$foo = new MatchRoute($route);

		$foo->CheckMatch("Foo/Bar");
		$keyValue = $foo->GetParameter('bar');

		return $this->Expects($keyValue)->ToBeEqualTo('Bar');
	}

	public function ItShouldFindTheValueOfAVariableKey()
	{
		return $this->Pending();
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":barid", "test");

		$foo = new MatchRoute($route);
		$foo->CheckMatch("Foo/tester/test");
		$keyValue = $foo->GetParameter('barid');

		return $this->Expects($keyValue)->ToBeEqualTo('tester');
	}
}
