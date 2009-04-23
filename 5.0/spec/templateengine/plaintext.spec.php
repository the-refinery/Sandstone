<?php

require('spechelper.php');

$fakeTemplate = new FakeTemplate();

$engine = new TemplateEngine();

$expectedRenderedTemplateContent = "Hello, World";

$fakeTemplate->content_to_return = $expectedRenderedTemplateContent;

$actualRenderedTemplateContent = $engine->Render($fakeTemplate);

assertEquals($expectedRenderedTemplateContent, $actualRenderedTemplateContent);
