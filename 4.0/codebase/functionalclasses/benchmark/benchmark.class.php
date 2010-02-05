<?php

class Benchmark extends Module
{
  protected $_benchmarks = array();

	static public function Instance()
	{
		static $benchmark;

		if (is_set($benchmark) == false)
		{
			$benchmark = new Benchmark();
		}

		return $benchmark;
	}

  static public function Log($Title, $Entry = null)
  {
    $benchmark = Benchmark::Instance();

    $returnValue = $benchmark->ProcessLog($Title, $Entry);

    return $returnValue;
  }

  static public function Start()
  {
    Benchmark::WriteLine("\n\n");
    Benchmark::Log("=== APPLICATION STARTED ===");
  }

  static public function Stop()
  {
    Benchmark::Log("=== APPLICATION STOPPED ===");

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

    reset($this->_benchmarks);
    $startTime = key($this->_benchmarks);
    end($this->_benchmarks);
    $endTime = key($this->_benchmarks);
    $totalElapsed = $endTime - $startTime;

    $this->WriteLineToFile("===================================");
    $this->WriteLineToFile("=== TOTAL BENCHMARKS: {$totalBenchmarks}");
    $this->WriteLineToFile("=== TOTAL ELAPSED: {$totalElapsed}");
    $this->WriteLineToFile("===================================");
  }

  public function ProcessLog($Title, $Entry = null)
  {
    $time = microtime(true);

    $logEntry = strtoupper($Title);
    $logEntry .= " ({$time}) ";
    $logEntry .= $Entry;

    $this->_benchmarks["{$time}"] = $logEntry;
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
