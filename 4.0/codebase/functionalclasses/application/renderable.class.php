<?php
/*
Renderable Class File

@package Sandstone
@subpackage Application
*/

class Renderable extends Module
{

    protected $_template;

	public function __construct()
	{
		$this->_template = new Template($this);
	}

    /*
    Template property

    @return Template
    */
    public function getTemplate()
    {
        return $this->_template;
    }

    public function Render()
	{
		return $this->_template->Render();
	}

    protected function CompressHTML($HTML, $IsReportAllowed = true)
    {
        //Do we compress this output?
        if (Application::Registry()->CompressHTML == 1 || Application::Registry()->ProductionCompressionMode == 1)
        {
			if ($IsReportAllowed && Application::Registry()->ReportHTMLcompression == 1 && Application::Registry()->ProductionCompressionMode != 1)
			{
				$isReportIncluded = true;
			}

			$this->CompressHTMLoutput($HTML, $isReportIncluded);
        }
        else
        {
            //No compression, just return the original string
            $returnValue = $HTML;
        }

        return $returnValue;


    }

	protected function CompressHTMLoutput($OutputString, $IsReportIncluded = false)
	{
        $removeLines = Array();

        //break the string into lines
        $lines = explode("\n", $OutputString);

        //Loop through each line
        for($i=0; $i < count($lines); $i++)
        {
            //Trim any leading or trailing spaces
            $lines[$i] = trim($lines[$i]);

            //Remove Empty Lines
            if (strlen($lines[$i]) == 0)
            {
                $removeLines[$i] = true;
            }
        }

        foreach ($removeLines as $key=>$value)
        {
            unset($lines[$key]);
        }

        //Put the lines back into a single string
        $returnValue = implode("", $lines);

        //Do we report compression results?
        if ($IsReportIncluded)
        {
            $sizeBefore = strlen($OutputString);
            $sizeAfter = strlen($returnValue);

            $delta = $sizeBefore - $sizeAfter;
            $percent = (round($delta / $sizeBefore, 3)) * 100;

            $compressionReport = "\n\n";
            $compressionReport .= "<!-- Original:   {$sizeBefore} bytes -->\n";
            $compressionReport .= "<!-- Compressed: {$sizeAfter} bytes -->\n";
            $compressionReport .= "<!-- Delta:      {$percent}% ({$delta} bytes) -->";

            $returnValue = $returnValue . $compressionReport;
        }

		return $returnValue;

	}

    protected function CompressJavascript($Javascript, $IsReportAllowed = true)
    {

        //Do we compress this output?
        if (Application::Registry()->CompressJavascript == 1 || Application::Registry()->ProductionCompressionMode == 1)
        {

            $isReportIncluded = false;

            if ($IsReportAllowed)
            {
                if (Application::Registry()->ReportJavascriptCompression == 1 && Application::Registry()->ProductionCompressionMode != 1)
                {
                    $isReportIncluded = true;
                }
            }

            $returnValue = $this->CompressOutput($Javascript, $isReportIncluded);
        }
        else
        {
            //No compression, just return the original string
            $returnValue = $Javascript;
        }

        return $returnValue;
    }

    protected function CompressCSS($CSS, $IsReportAllowed = true)
    {

        //Do we compress this output?
        if (Application::Registry()->CompressCSS == 1 || Application::Registry()->ProductionCompressionMode == 1)
        {

            $isReportIncluded = false;

            if ($IsReportAllowed)
            {
                if (Application::Registry()->ReportCSScompression == 1 && Application::Registry()->ProductionCompressionMode != 1)
                {
                    $isReportIncluded = true;
                }
            }

            $returnValue = $this->CompressOutput($CSS, $isReportIncluded);
        }
        else
        {
            //No compression, just return the original string
            $returnValue = $CSS;
        }

        return $returnValue;
    }

    protected function CompressOutput($OutputString, $IsReportIncluded = false)
    {

        $removeLines = Array();

        //break the string into lines
        $lines = explode("\n", $OutputString);

        //Loop through each line
        for($i=0; $i < count($lines); $i++)
        {
            //Trim any leading or trailing spaces
            $lines[$i] = trim($lines[$i]);

            //Remove Empty Lines
            if (strlen($lines[$i]) == 0)
            {
                $removeLines[$i] = true;
            }

            //Remove Single Line Comments
            if (substr($lines[$i], 0, 2) == "//")
            {
                $removeLines[$i] = true;
            }

            //Look for the start of Multi line comments
            if (substr($lines[$i], 0, 2) == "/*")
            {
                $removeLines[$i] = true;
                $isMultiLineActive = true;
            }

            //Look for end of multi line comments
            if ($isMultiLineActive)
            {
                if (strpos($lines[$i], "*/") === false)
                {
                    //Still in the comment, keep procesing
                    $removeLines[$i] = true;
                }
                else
                {
                    //Found the end of the comment.
                    $isMultiLineActive = false;
                    $removeLines[$i] = true;
                }
            }
        }

        foreach ($removeLines as $key=>$value)
        {
            unset($lines[$key]);
        }

        //Put the lines back into a single string
        $returnValue = implode(" ", $lines);

        //Remove any spaces before or after a { or }
        $returnValue = str_replace(array(" {", " }"), array("{", "}"), $returnValue);
        $returnValue = str_replace(array("{ ", "} "), array("{", "}"), $returnValue);

        //Do we report compression results?
        if ($IsReportIncluded)
        {
            $sizeBefore = strlen($OutputString);
            $sizeAfter = strlen($returnValue);

            $delta = $sizeBefore - $sizeAfter;
            $percent = (round($delta / $sizeBefore, 3)) * 100;

            $compressionReport = "/* Original:   {$sizeBefore} bytes */\n";
            $compressionReport .= "/* Compressed: {$sizeAfter} bytes */\n";
            $compressionReport .= "/* Delta:      {$percent}% ({$delta} bytes) */\n\n";

            $returnValue = $compressionReport . $returnValue;
        }

        return $returnValue;
    }

	protected function Terminalize($TerminalOutput)
	{
		// Our bash runner reads the output of this template and sources it with bash.  
		// It needs to pass an unescaped newline character in addition to the escaped one, 
		// which is interpreted by the bash source command
		
		$TerminalOutput = str_replace("\n","\\n\n", $TerminalOutput);
		return $TerminalOutput;
	}

}

?>