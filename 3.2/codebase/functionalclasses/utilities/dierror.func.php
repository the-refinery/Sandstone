<?php

function DIErrorHandler ($ErrorLevel, $ErrorMessage, $File, $Line) 
{
    $die = false;

    // Only handle the errors specified by the error_reporting directive or function
    // Ensure that we should be displaying and/or logging errors
    if ( ! ($ErrorLevel & error_reporting ()) || ! (ini_get ('display_errors') || ini_get ('log_errors')))
	{
		return;
	}

    // Give the error level a name
    switch ($ErrorLevel) 
	{
        case E_NOTICE:
        case E_USER_NOTICE:
            $ErrorType = 'Notice';
            break;

        case E_WARNING:
        case E_USER_WARNING:
            $ErrorType = 'Warning';
            break;

        case E_ERROR:
        case E_USER_ERROR:
            $ErrorType = 'Fatal Error';
            $die = true;
            break;

        // Handle the possibility of new error constants being added
        default:
            $ErrorType = 'Unknown';
            $die = true;
            break;
    }

    if (ini_get ('display_errors'))
	{
		displayError($ErrorLevel, $ErrorType, $ErrorMessage, $File, $Line);
	}

    if (ini_get ('log_errors'))
	{
        error_log (sprintf ("%s: %s in %s on line %d", $ErrorType, $ErrorMessage, $File, $Line));		
	}

    if ($die == true)
	{
        die();		
	}
}

function displayError($ErrorLevel, $ErrorType, $ErrorMessage, $File, $Line)
{
	switch ($ErrorLevel) 
	{
        case E_NOTICE:
        case E_USER_NOTICE:
            $color = '#ccf';
			$border = '#3c6';
            break;

        case E_WARNING:
        case E_USER_WARNING:
            $color = '#ffc';
			$border = '#f93';
            break;

        case E_ERROR:
        case E_USER_ERROR:
            $color = '#ffb8ac';
			$border = '#f30';
            break;

        // Handle the possibility of new error constants being added
        default:
            $color = '#ccf';
			$border = '#3c6';
            break;
    }

	echo "<div style=\"padding:6px; border:1px solid $border; background-color:$color \">";
	echo "<h1 style=\"padding:0; margin:0; border-bottom:1px solid #000;\">$ErrorType</h1>";
	echo "<p>$ErrorMessage</p>";
	echo "<h2 style=\"font-weight:normal; font-size:1em;\"> <b>Line:$Line</b>  $File.</h2>";
	echo "</div>";
}

?>