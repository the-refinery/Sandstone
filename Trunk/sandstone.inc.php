<?php
/**
 * Sandstone Include File
 * @package Sandstone
 * @subpackage 
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

//Error handling functions are required, so explicitely define them
require("codebase/functionalclasses/utilities/dierror.func.php");

//These base classes are foundational to everything we do, 
//so we explicitly require them here.
require("codebase/baseclasses/component.class.php");
require("codebase/baseclasses/debug.class.php");
require("codebase/baseclasses/module.class.php");
require("codebase/baseclasses/namespace.class.php");
require("codebase/baseclasses/activerecord.class.php");
require("codebase/baseclasses/diarray.class.php");

//These are also foundational, so we will explicitly require them here too.
require("codebase/functionalclasses/utilities/file.func.php");

//We will override the AutoLoad function to allow us to dynamically load individual
//class files as needed from the Namespaces.  This gives a major performance increase.
function __autoload($ClassName)
{	
	NameSpace::AutoLoad($ClassName);
}

?>