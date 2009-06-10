<?php

class RoutingSpec extends DescribeBehavior
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

	public function ItShouldMatchAStaticRoute()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar");

		return $this->Expects($match)->ToBeTrue();
	}

	public function ItShouldMatchAStaticRouteWithMixedCase()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("FOO/BAR");

		return $this->Expects($match)->ToBeTrue();
	}

	public function ItShouldMatchWithATrailingSlash()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar/");

		return $this->Expects($match)->ToBeTrue();
	}

	public function ItShouldMatchWithAFileType()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar.htm");

		return $this->Expects($match)->ToBeTrue();
	}

	public function ItShouldRecordTheFileType()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar.css");

		return $this->Expects($foo->FileType)->ToBeEqualTo('css');
	}

	public function ItShouldUseHtmFileTypeByDefault()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar");

		return $this->Expects($foo->FileType)->ToBeEqualTo('htm');
	}
}
