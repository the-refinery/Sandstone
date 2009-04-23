<?php

function assertEquals($Expected, $Actual)
{
	if ($Expected != $Actual)
	{
		throw new Exception("Expected $Expected, got $Actual");
	}
}

