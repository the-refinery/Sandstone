<?php

require('codebase/core/handleerrors.php');
set_error_handler("HandleError", E_ALL);

//directly include files necessary for namespacing to work
include_once("codebase/lib/baseclasses/base.class.php");
include_once("codebase/lib/baseclasses/baseprimitive.class.php");
include_once("codebase/lib/baseclasses/baseservice.class.php");
include_once("codebase/lib/baseclasses/basesingleton.class.php");
include_once("codebase/core/filesystem/parseadirectory.service.php");
include_once("codebase/lib/namespace/namespace.service.php");

SandstoneNamespace::Using("lib.alterclass");
SandstoneNamespace::Using("lib.dispatchapplication");
SandstoneNamespace::Using("lib.routing");
SandstoneNamespace::Using("lib.httprequest");
SandstoneNamespace::Using("lib.rest");
SandstoneNamespace::Using("core.string");
SandstoneNamespace::Using("core.console");
SandstoneNamespace::Using("core.benchmark");
