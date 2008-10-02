<?php
/*
Markdown plugin extension

@package Sandstone
@subpackage Markdown
*/

function DImarkdown($Value)
{

	$returnValue = Markdown($Value);

	$returnValue = str_replace(Array("\n", "\t"), "", $returnValue);

	return $returnValue;
}

?>
