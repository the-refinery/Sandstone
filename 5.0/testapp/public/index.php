<?php

require("../../codebase/core/templateengine/filetemplate.class.php");
require("../../codebase/core/templateengine/templateengine.class.php");

$currentRoute = $_GET['route'];
$currentAction = $_GET['action'];

$fileName = "../app/views/{$currentRoute}/{$currentAction}.htm.template";

$FileTemplate = new FileTemplate($fileName);
$TemplateEngine = new TemplateEngine();

echo $TemplateEngine->Render($FileTemplate);
