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

	public function ItShouldMatchAStaticRoute()
	{
		$foo = new Route("Foo/Bar");
		$match = $foo->CheckRoutingMatch("foo/bar");

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

	public function ItShouldHaveNamedKeysForEachParameter()
	{
		$foo = new Route("Home");

		return $this->Expects($foo->Parameters)->ToHaveKey("home");
	}

	public function ItShouldHaveNamedKeysForVariableParameters()
	{
		$foo = new Route("Foo/:FooID");

		return $this->Expects($foo->Parameters)->ToHaveKey('fooid');
	}

	public function ItShouldBuildARegexStringForStaticRoutes()
	{
		$foo = new Route("Foo/Bar");
		$pattern = $foo->GenerateMatchPattern($foo->Parameters);

		return $this->Expects($pattern)->ToBeEqualTo('@^foo/bar$@i');
	}

	public function ItShouldBuildARegexStringForVariableRoutes()
	{
		$foo = new Route("Foo/:FooID");
		$pattern = $foo->GenerateMatchPattern($foo->Parameters);

		return $this->Expects($pattern)->ToBeEqualTo("@^foo/[a-zA-Z0-9_-]+$@i");
	}

	public function ItShouldReportTheDefinedPath()
	{
		$foo = new Route("Foo/:FooID");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/:fooid');
	}

	public function ItShouldAllowNonUrlParameters()
	{
		$foo = new Route("Foo/:FooID");
		$foo->AddParameter('Gorp', true);

		return $this->Expects($foo->Parameters)->ToHaveKey('gorp');
	}
}
