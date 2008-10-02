<?php

function Lookup($Class, $Method = "All", $Parameters = Array(), $PageSize = null , $PageNumber = null)
{

	$target = new $Class ();

    //Make sure our parameter keys are lowercase
    $Parameters = DIarray::ForceLowercaseKeys($Parameters);

	$returnValue = $target->Lookup($Class, $Method, $Parameters, $PageSize, $PageNumber);

	return $returnValue;

}

function LookupCount($Class, $Method = "All", $Parameters = Array())
{

	$target = new $Class ();

    //Make sure our parameter keys are lowercase
    $Parameters = DIarray::ForceLowercaseKeys($Parameters);

	$returnValue = $target->LookupCount($Class, $Method, $Parameters);

	return $returnValue;

}

function LookupObjectSet($Class, $Method = "All", $Parameters = Array(), $PageSize = null , $PageNumber = null)
{

	$target = new $Class ();

	$ds = $target->Lookup($Class, $Method, $Parameters, $PageSize, $PageNumber);

	$returnValue = new ObjectSet($ds, $Class, $target->PrimaryIDproperty->Name);

	return $returnValue;
}

?>
