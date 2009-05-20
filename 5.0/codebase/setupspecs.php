<?php

// Include codebase
$currentPath = dirname(__FILE__);
include_once($currentPath . "/../include.php");

Namespace::Using("lib.spec");

// Include the specs to run
Namespace::Using("core.string.spec");
Namespace::Using("core.console.spec");
Namespace::Using("core.filesystem.spec");
Namespace::Using("lib.spec.spec");
Namespace::Using("lib.alterclass.spec");
Namespace::Using("lib.baseclasses.spec");
Namespace::Using("lib.namespace.spec");

