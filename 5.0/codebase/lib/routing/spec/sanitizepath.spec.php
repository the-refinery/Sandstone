<?php

class SanitizePathSpec extends DescribeBehavior
{
	public function ItShouldSanitizeAStaticRouteWithMixedCase()
	{
		$foo = new SanitizePath("FOO/BAR");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithATrailingSlash()
	{
		$foo = new SanitizePath("foo/bar/");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithBlankParameters()
	{
		$foo = new SanitizePath("foo//bar//");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithAFileType()
	{
		$foo = new SanitizePath("foo/bar.htm");

		return $this->Expects($foo->Path)->ToBeEqualTo('foo/bar');
	}
}
