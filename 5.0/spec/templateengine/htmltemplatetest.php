<?php

require('spechelper.php');

class HTMLTemplateTest extends PHPUnit_Framework_TestCase
{
	public function testRenderATemplate()
	{
		$fakeTemplate = new FakeTemplate();
		$engine = new TemplateEngine();

		$expectedRenderedTemplateContent = "<h1>Hello, World</h1>";

		$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

		$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

		$this->assertEquals($expectedRenderedTemplateContent, $actualRenderedTemplateContent);
	}
}

