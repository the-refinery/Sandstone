<?php

class MatchRouteSpec extends DescribeBehavior
{
	public function ItShouldSplitAStaticRouteIntoPieces()
	{
		$route = new Route("foo/bar");
		$foo = new MatchRoute($route);

		return $this->Expects($foo->Keys[1])->ToBeEqualTo('bar');
	}

	public function ItShouldSplitADynamicRouteIntoPieces()
	{
		$route = new Route("foo/:fooid");
		$foo = new MatchRoute($route);

		return $this->Expects($foo->Keys[1])->ToBeEqualTo('fooid');
	}

	public function ItShouldCreateAMatchStringForDynamicRoute()
	{
		$route = new Route("foo/:fooid/test/:testid");
		$foo = new MatchRoute($route);

		return $this->Expects($foo->MatchPattern)->ToBeEqualTo('@^foo/[a-zA-Z0-9_-]+/test/[a-zA-Z0-9_-]+$@i');
	}

	public function ItShouldMatchAStaticRoute()
	{
		$route = new Route("foo/bar");
		$foo = new MatchRoute($route);

		$check = $foo->CheckMatch("Foo/BAR");
		return $this->Expects($check)->ToBeTrue();
	}

	public function ItShouldMatchADynaicRoute()
	{
		$route = new Route("foo/:fooid/bar/:barid");
		$foo = new MatchRoute($route);

		$check = $foo->CheckMatch("Foo/5/bar/3");
		return $this->Expects($check)->ToBeTrue();
	}
}
