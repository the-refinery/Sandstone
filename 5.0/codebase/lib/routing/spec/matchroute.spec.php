<?php

class MatchRouteSpec extends DescribeBehavior
{
	public function ItShouldSplitAStaticRouteIntoPieces()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", "bar");

		$foo = new MatchRoute($route);

		return $this->Expects($foo->Keys[1])->ToBeEqualTo('bar');
	}

	public function ItShouldSplitADynamicRouteIntoPieces()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":fooid");

		$foo = new MatchRoute($route);

		return $this->Expects($foo->Keys[1])->ToBeEqualTo('fooid');
	}

	public function ItShouldCreateAMatchStringForDynamicRoute()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":fooid", "test", ":testid");

		$foo = new MatchRoute($route);

		return $this->Expects($foo->MatchPattern)->ToBeEqualTo('@^foo/[a-zA-Z0-9_-]+/test/[a-zA-Z0-9_-]+$@i');
	}

	public function ItShouldMatchAStaticRoute()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", "bar");

		$foo = new MatchRoute($route);

		$check = $foo->CheckMatch("Foo/BAR");
		return $this->Expects($check)->ToBeTrue();
	}

	public function ItShouldMatchADynamicRoute()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":fooid", "bar", ":barid");

		$foo = new MatchRoute($route);
		$check = $foo->CheckMatch("Foo/5/bar/3");

		return $this->Expects($check)->ToBeTrue();
	}

	public function ItShouldFindTheValueOfAKey()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", "bar");

		$foo = new MatchRoute($route);

		$foo->CheckMatch("Foo/Bar");
		$keyValue = $foo->GetParameter('bar');

		return $this->Expects($keyValue)->ToBeEqualTo('Bar');
	}

	public function ItShouldFindTheValueOfAVariableKey()
	{
		$route = new Mock('Route');
		$route->Parameters = array("foo", ":barid", "test");

		$foo = new MatchRoute($route);
		$foo->CheckMatch("Foo/tester/test");
		$keyValue = $foo->GetParameter('barid');

		return $this->Expects($keyValue)->ToBeEqualTo('tester');
	}
}
