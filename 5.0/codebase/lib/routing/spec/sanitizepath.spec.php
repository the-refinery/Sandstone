<?php

class SanitizePathSpec extends DescribeBehavior
{
	public function ItShouldSanitizeAStaticRouteWithMixedCase()
	{
		$results = SanitizePath::Sanitize("FOO/BAR");

		return $this->Expects($results)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithATrailingSlash()
	{
		$results = SanitizePath::Sanitize("foo/bar/");

		return $this->Expects($results)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithBlankParameters()
	{
		$results = SanitizePath::Sanitize("foo//bar//");

		return $this->Expects($results)->ToBeEqualTo('foo/bar');
	}

	public function ItShouldSanitizeWithAFileType()
	{
		$results = SanitizePath::Sanitize("foo/bar.htm");

		return $this->Expects($results)->ToBeEqualTo('foo/bar');
	}
}
