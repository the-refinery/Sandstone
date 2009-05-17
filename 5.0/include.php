<?php

require('codebase/core/handleerrors.php');
set_error_handler("HandleError", E_ALL);

include_once("codebase/lib/baseclasses/component.class.php");
include_once("codebase/lib/alterclass/include.php");
include_once("codebase/lib/namespace/namespace.class.php");
include_once("codebase/lib/spec/include.php");
include_once("codebase/core/string/formatstring.class.php");
include_once("codebase/core/console/outputtobash.class.php");
