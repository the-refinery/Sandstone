<?php

class Benchmark extends Module
{
  protected $_benchmarks = array();
  protected $_types = array();

  protected $_starttime;
  protected $_endtime;

  public function __construct()
  {
    $this->_starttime = microtime(true);
  }

	static public function Instance()
	{
		static $benchmark;

		if (is_set($benchmark) == false)
		{
			$benchmark = new Benchmark();
		}

		return $benchmark;
	}

  static public function Log($Title, $Entry = null, $IsSummarized = true)
  {
    $benchmark = Benchmark::Instance();

    $returnValue = $benchmark->ProcessLog($Title, $Entry, $IsSummarized);

    return $returnValue;
  }

  static public function Start()
  {
    Benchmark::WriteLine("\n\n");
    Benchmark::Log("=== APPLICATION STARTED ===", null, false);
  }

  static public function Stop()
  {
    Benchmark::Log("=== APPLICATION STOPPED ===", null, false);

    Benchmark::Summarize();
  }

  static public function Summarize()
  {
    $benchmark = Benchmark::Instance();

    $returnValue = $benchmark->ProcessSummarize();

    return $returnValue;
  }

  static public function WriteLine($LogEntry)
  {
    $benchmark = Benchmark::Instance();

    $returnValue = $benchmark->WriteLineToFile($LogEntry);

    return $returnValue;
  }

  public function ProcessSummarize()
  {
    $totalBenchmarks = count($this->_benchmarks);

    $elapsed = $this->_endtime - $this->_starttime;
    $elapsed = number_format($elapsed, 3);

    $this->WriteLineToFile("===================================");
    $this->WriteLineToFile("=== TOTAL BENCHMARKS: {$totalBenchmarks}");

    foreach ($this->_types as $type => $count)
    {
      $this->WriteLineToFile("=== {$type}: {$count}");
    }

    $this->WriteLineToFile("=== TOTAL ELAPSED: {$elapsed} sec");
    $this->WriteLineToFile("===================================");
  }

  public function ProcessLog($Title, $Entry = null, $IsSummarized = true)
  {
    $time = microtime(true);

    $Title = strtoupper($Title);

    $Entry = str_replace("\n","", $Entry);
    $Entry = preg_replace('/\s+/', ' ', $Entry);

    $logEntry = $Title;
    $logEntry .= " ({$time}) ";
    $logEntry .= $Entry;

    if ($IsSummarized)
    {
      $this->_benchmarks[] = $logEntry;
      $this->_types[$Title]++;
    }

    $this->_endtime = $time;
    $this->WriteLineToFile($logEntry);
  }

  private function FetchLogFile()
  {
    GLOBAL $APPLICATION_ROOT_LOCATION;
    $logSpec = Application::Registry()->LogFile;

    if (strlen($logSpec) > 0)
    {
      $returnValue = $APPLICATION_ROOT_LOCATION . $logSpec;
    }

    return $returnValue;
  }

  private function WriteLineToFile($LogEntry)
  {
    $logFileSpec = $this->FetchLogFile();

    if ($logFileSpec)
    {
      $handle = fopen($logFileSpec, 'a');

      fwrite($handle, $LogEntry . "\n"); 
      fclose($handle); 
    }
  }
}
