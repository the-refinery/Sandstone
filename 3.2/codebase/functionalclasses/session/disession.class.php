<?php
/*
Session Handler Class File

@package Sandstone
@subpackage Session
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class DISession
{
	protected $_sessionDBconfig = Array(	"DBhost" => "localhost",
											"DBuser" => "bacdata_session",
											"DBpass" => "jaw1206",
											"DBname" => "bacdata_session");

	protected $_conn;
	protected $_timeout;

	public function __construct()
	{
		session_set_save_handler(
			array($this,"Open"),
	      	array($this,"Close"),
		    array($this,"Read"),
		    array($this,"Write"),
		    array($this,"Destroy"),
		    array($this,"GarbageCollector")
		);

		$this->_timeout = 86400; // 1 day
	}

	public function Open($savePath, $sessionName)
	{
	    //connect to the database
	    $conn = GetConnection($this->_sessionDBconfig);

		// Default to true
		$returnValue = true;

	    if(! $conn)
		{
	        $returnValue = false;
	    }

	    $this->_conn = $conn;
		
	    return $returnValue;
	}

	public function Close()
	{
	    $this->GarbageCollector($this->_timeout);
	}

	public function Read($sessionID)
	{
		//fetch the session record
	    $query = "SELECT data FROM sessionMaster
	              WHERE sessionid = '" . mysql_real_escape_string($sessionID) . "'";

		$ds = $this->_conn->Execute($query);

	    if($dr = $ds->FetchRow())
		{
	        return $dr['data'];
	    }

	    // you MUST send an empty string if no session data, not NULL
	    return "";
	}

	public function Write($sessionID, $data)
	{
		$this->Destroy($sessionID);

		$tempSessionID = mysql_real_escape_string($sessionID);
		$tempSessionData = mysql_real_escape_string($data);
		$tempSessionTime = time();

	    $query = "INSERT INTO sessionMaster
					(
						sessionid,
						lastaccess,
						data
					)
					VALUES
					(
						'{$tempSessionID}',
						{$tempSessionTime},
						'{$tempSessionData}'
					)";

		$ds = $this->_conn->Execute($query);

	    return true;
	}

	public function Destroy($sessionID) {
		//remove session record from the database and return result
	    $query = "DELETE FROM sessionMaster
	                WHERE sessionid = '".mysql_real_escape_string($sessionID)."'";

		$ds = $this->_conn->Execute($query);

	    return true;
	}

	public function GarbageCollector($maxLifeTime = null)
	{
		if (is_set($maxLifeTime) == false)
		{
			$maxLifeTime = $this->_timeout;
		}

	    $timeout = time() - $maxLifeTime;

	    $query = "DELETE FROM sessionMaster
	                    WHERE lastaccess < {$timeout}";

		$ds = $this->_conn->Execute($query);

	    return  $this->_conn->Affected_Rows();
	}
}
?>