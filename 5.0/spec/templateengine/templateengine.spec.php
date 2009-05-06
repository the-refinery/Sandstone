<?php

require('dependencies.php');

class TemplateEngineSpec extends DISpec
{
	public function ItShouldRenderAPlainTextTemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$expectedRenderedTemplateContent = "<h1>Hello, World</h1>";

		$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

		$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

		$this->Expects($expectedRenderedTemplateContent)->ToBeEqualTo($actualRenderedTemplateContent);
	}

	public function ItShouldRenderAnHtmlTemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$expectedRenderedTemplateContent = "Hello, World";

		$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

		$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

		$this->Expects($expectedRenderedTemplateContent)->ToBeEqualTo($actualRenderedTemplateContent);
	}
}

$Spec = new TemplateEngineSpec();
$Spec->Run();
