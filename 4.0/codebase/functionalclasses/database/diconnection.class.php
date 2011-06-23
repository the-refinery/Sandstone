<?php

SandstoneNamespace::Using("Sandstone.Date");
SandstoneNamespace::Using("Sandstone.Exception");

class DIConnection extends mysqli
{

	protected $_host;
	protected $_username;
	protected $_password;
	protected $_dbname;
	protected $_port;
	protected $_socket;

	protected $_totalDBtime;
	protected $_lastQueryExecuteTime;

	protected $_isLoggingEnabled;
	protected $_queryLog;
	protected $_queryCallCount;


	public function __construct($host = null, $username = null, $passwd = null, $dbname = null, $port = null, $socket  = null)
	{
		parent::__construct($host, $username, $passwd, $dbname, $port, $socket);

		$this->_host = $host;
		$this->_username = $username;
		$this->_password = $passwd;
		$this->_dbname = $dbname;
		$this->_port = $port;
		$this->_socket = $socket;

		$this->_totalDBtime = 0;
		$this->_lastQueryExecuteTime = 0;

		if (Application::Registry()->DatabaseLogging == true)
		{
			$this->_isLoggingEnabled = true;
			$this->_queryLog = new DIarray();
			$this->_queryCallCount = new DIarray();
		}
		else
		{
			$this->_isLoggingEnabled = false;
		}
	}

	public function __destruct()
	{
		if ($this->_isLoggingEnabled)
		{
			$this->ShowQueryLog();
		}


		$this->Close();
	}

	public function __toString()
	{
		$returnValue = "{$this->_username}@{$this->_host} (DB: {$this->_dbname})";

		return $returnValue;
	}

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this, $getter))
		{
			$returnValue = $this->$getter();
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}

		return $returnValue;
	}

	/*
	TotalDBtime property

	@return decimal
	 */
	public function getTotalDBtime()
	{
		return $this->_totalDBtime;
	}

	/*
	LastQueryExecuteTime property

	@return decimal
	 */
	public function getLastQueryExecuteTime()
	{
		return $this->_lastQueryExecuteTime;
	}

	/*
	Host property

	@return string
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/*
	Username property

	@return string
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/*
	Password property

	@return string
	 */
	public function getPassword()
	{
		return $this->_password;
	}

	/*
	Dbname property

	@return string
	 */
	public function getDbname()
	{
		return $this->_dbname;
	}

	/*
	Port property

	@return string
	 */
	public function getPort()
	{
		return $this->_port;
	}

	/*
	Socket property

	@return string
	 */
	public function getSocket()
	{
		return $this->_socket;
	}

	/*
	QueryLog property

	@return DIarray
	 */
	public function getQueryLog()
	{
		return $this->_queryLog;
	}

	public function connect($host = null, $username = null, $passwd = null, $dbname = null, $port = null, $socket  = null)
	{
		parent::connect($host, $username, $passwd, $dbname, $port, $socket);

		$this->_host = $host;
		$this->_username = $username;
		$this->_password = $passwd;
		$this->_dbname = $dbname;
		$this->_port = $port;
		$this->_socket = $socket;

	}

	public function query($query, $resultmode = MYSQLI_STORE_RESULT)
	{
		$startTime = microtime(true);

		$returnValue = parent::query($query, $resultmode);

		$endTime = microtime(true);

		$this->_lastQueryExecuteTime = $endTime - $startTime;
		$this->_totalDBtime += $this->_lastQueryExecuteTime;

		if (is_set($this->affected_rows))
		{
			$recordCount = $this->affected_rows;
		}
		else
		{
			$recordCount = $returnValue->num_rows;
		}

		$this->LogQuery($query, $recordCount);

		return $returnValue;
	}

	protected function LogQuery($Query, $RecordCount)
	{
    Benchmark::Log("Database","{$Query}");
		if ($this->_isLoggingEnabled)
		{
	        $callStack = debug_backtrace();

    		//The call context we are interested in will be index 3 in the array.
    		// 0 = this function
    		// 1 = internal query method
    		// 2 = query object Execute
    		// 3 = original calling method
			$context = $callStack[3];

			$class = get_class($context['object']);
			$callingClass = $context['class'];
			$method = $context['function'];
			$line = $callStack[2]['line'];

			$newLog = new QueryLog($class, $callingClass, $method, $line, $Query, $this->_lastQueryExecuteTime, $RecordCount);

			$this->_queryLog[] = $newLog;


			if (array_key_exists(strtolower($Query), $this->_queryCallCount))
			{
				$this->_queryCallCount[strtolower($Query)]++;
			}
			else
			{
				$this->_queryCallCount[strtolower($Query)] = 1;
			}
		}
	}

	public function ShowQueryLog()
	{
		if ($this->_queryLog instanceof DIarray)
		{

			$style = "style=\"border-width: 1px;
						padding: 3px 5px;
						background-color: white;
						border-color: gray; \" ";

			echo "<div style=\"border: 0; background-color: #39f; padding: 6px; margin: 10px;\">";
			echo "<h1>Database Query Log</h1>";

			echo "<h2>Database: {$this->_dbname}</h2>";
			echo "<h2>User: {$this->_username}@{$this->_host}</h2>";

			$count = count($this->_queryLog);
			$totalTime = round($this->_totalDBtime, 6);
			echo "<h3>Query Count: {$count}</h3>";
			echo "<h3>Total DB time: {$totalTime}</h3>";

			echo "<table>";
			echo "	<tr>
						<th {$style}>#</th>
						<th {$style}>Class</th>
						<th {$style}>Method</th>
						<th {$style}># Rec's</th>
						<th {$style}>Time</th>
						<th {$style}>Time %</th>
						<th {$style}>SQL</th>
						<th {$style}>Calls</th>
					</tr>";

			foreach ($this->_queryLog as $index=>$tempLog)
			{
				$time = round($tempLog->ExecutionTime, 6);
				$timePercent = round(($tempLog->ExecutionTime / $this->_totalDBtime) * 100 , 1);
				$count = $this->_queryCallCount[strtolower($tempLog->SQL)];

				echo "<tr>";

				echo "<td {$style}>{$index}</td>";
				echo "<td {$style}>{$tempLog->Class}</td>";
				echo "<td {$style}>{$tempLog->CallingClass}->{$tempLog->Method}()<br /><i>Line: {$tempLog->Line}</i></td>";
				echo "<td {$style}>{$tempLog->RecordCount}</td>";
				echo "<td {$style}>{$time}</td>";
				echo "<td {$style}>{$timePercent}%</td>";
				echo "<td {$style}>{$tempLog->SQL}</td>";
				echo "<td {$style}>{$count}</td>";

				echo "</tr>";
			}

            echo "</table>";
            echo "</div>";

		}
	}
}
?>
