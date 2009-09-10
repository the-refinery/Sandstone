<?php
/*
File Function File

@package Sandstone
@subpackage Utilities
*/

function ReadFileContents($FileSpec, $UseIncludeFilePath = false, $IncludeBlankLines = false)
{

	$handle = @fopen($FileSpec, "r", $UseIncludeFilePath);

	if ($handle)
	{
		while (!feof($handle))
		{
			$buffer = fgets($handle, 4096);

			if (strlen($buffer) > 0)
			{
				$returnValue[] = rtrim($buffer);
			}
			else
			{
				if ($IncludeBlankLines == true)
				{
					$returnValue[] = rtrim($buffer);
				}
			}
		}

	   fclose($handle);
	}
	else
	{
		$returnValue = null;
	}

	return $returnValue;
}


function file_exists_incpath($FileName)
{
    $paths = explode(PATH_SEPARATOR, get_include_path());

    $returnValue = false;

    foreach ($paths as $path)
    {
        // Formulate the absolute path
        $fullpath = $path . DIRECTORY_SEPARATOR . $FileName;

        // Check it
        if (file_exists($fullpath))
        {
            $returnValue = $fullpath;
        }
    }

    return $returnValue;
}

function ResolveFullDirectoryPath($Directory)
{

		$pathArray = explode(PATH_SEPARATOR, get_include_path());

		$i = 0;
		$isFound = false;

		while ($i < count($pathArray) && $isFound == false)
		{
			$testPath = $pathArray[$i] . $Directory;

			if (is_dir($testPath))
			{
				$isFound = true;
			}
			else
			{
				$i++;
			}
		}

		if ($isFound)
		{
			$returnValue = $testPath;

			if (substr($returnValue, -1) != "/")
			{
				$returnValue .= "/";
			}

		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
}

?>
