<?php

require('codebase/core/handleerrors.php');
set_error_handler("HandleError", E_ALL);

//directly include files necessary for namespacing to work
include_once("codebase/lib/baseclasses/component.class.php");
include_once("codebase/core/filesystem/parseadirectory.service.php");
include_once("codebase/lib/namespace/namespace.service.php");

Namespace::Using("lib.alterclass");
Namespace::Using("lib.httprequest");
Namespace::Using("lib.rest");
Namespace::Using("core.string");
Namespace::Using("core.console");
Namespace::Using("core.benchmark");
