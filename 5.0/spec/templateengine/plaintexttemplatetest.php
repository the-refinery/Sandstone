<?php

require('spechelper.php');

class PlainTextTemplateTest extends PHPUnit_Framework_TestCase
{
	public function testRenderATemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$expectedRenderedTemplateContent = "Hello, World";

		$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

		$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

		$this->assertEquals($expectedRenderedTemplateContent, $actualRenderedTemplateContent);
	}
}

