<?php

include('dependencies.php');

class TemplateEngineSpec extends DISpecSuite
{
	public function ItShouldRenderAPlainTextTemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$fakeTemplate->content_to_return = "Hello, World";

		return $this->Expects($engine->Render($fakeTemplate))->ToBeEqualTo("Hello, World");
	}

	public function ItShouldRenderAnHtmlTemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$fakeTemplate->content_to_return = "<h1>Hello, World</h1>";

		return $this->Expects($engine->Render($fakeTemplate))->ToBeEqualTo("<h1>Hello, World</h1>");
	}
}
