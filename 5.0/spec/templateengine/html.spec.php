<?php

require('spechelper.php');

$fakeTemplate = new FakeTemplate();

$engine = new TemplateEngine();

$expectedRenderedTemplateContent = "<h1>Hello World</h1>";

$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

assertEquals($expectedRenderedTemplateContent, $actualRenderedTemplateContent);
