<?php

// Include codebase
$currentPath = dirname(__FILE__);
include_once($currentPath . "/../include.php");

SandstoneNamespace::Using("lib.spec");

// Include the specs to run
SandstoneNamespace::Using("core.benchmark.spec");
SandstoneNamespace::Using("core.console.spec");
SandstoneNamespace::Using("core.filesystem.spec");
SandstoneNamespace::Using("core.string.spec");

SandstoneNamespace::Using("lib.alterclass.spec");
SandstoneNamespace::Using("lib.baseclasses.spec");
SandstoneNamespace::Using("lib.dispatchapplication.spec");
SandstoneNamespace::Using("lib.namespace.spec");
SandstoneNamespace::Using("lib.rest.spec");
SandstoneNamespace::Using("lib.routing.spec");
SandstoneNamespace::Using("lib.spec.spec");
