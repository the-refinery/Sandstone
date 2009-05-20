<?php

require('codebase/core/handleerrors.php');
set_error_handler("HandleError", E_ALL);

//directly include files necessary for namespacing to work
include_once("codebase/lib/baseclasses/component.class.php");
include_once("codebase/core/filesystem/parseadirectory.class.php");
include_once("codebase/lib/namespace/namespace.class.php");

Namespace::Using("lib.alterclass");
Namespace::Using("core.string");
Namespace::Using("core.console");
