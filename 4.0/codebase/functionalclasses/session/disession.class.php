<?php
/*
Session Handler Class File

@package Sandstone
@subpackage Session
*/

class DISession
{
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

		$returnValue = false;

	    //connect to the database
		$this->_conn = new mysqli("localhost", "bacdata_session", "jaw1206", "bacdata_session");

		if (is_set($this->_conn))
		{
			$returnValue = true;
		}

	    return $returnValue;
	}

	public function Close()
	{
	    $this->GarbageCollector($this->_timeout);
	}

	public function Read($sessionID)
	{
		//fetch the session record
	    $query = "	SELECT	data
	    			FROM 	sessionMaster
	              	WHERE 	sessionid = '" . $this->_conn->real_escape_string($sessionID) . "'";

		$result = $this->_conn->query($query);

		if ($this->_conn->errno == 0)
		{
			if ($result->num_rows > 0)
			{
				$dr = $result->fetch_array(MYSQLI_ASSOC);
			}

			$returnValue = $dr['data'];

	    }
	    else
	    {
	    	// you MUST send an empty string if no session data, not NULL
	    	$returnValue = "";
	    }

	    return $returnValue;
	}

	public function Write($sessionID, $data)
	{
		$this->Destroy($sessionID);

		$tempSessionID = $this->_conn->real_escape_string($sessionID);
		$tempSessionData = $this->_conn->real_escape_string($data);
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

		$this->_conn->query($query);

	    return true;
	}

	public function Destroy($sessionID)
	{
		//remove session record from the database and return result
	    $query = "	DELETE
	    			FROM 	sessionMaster
	                WHERE 	sessionid = '" . $this->_conn->real_escape_string($sessionID) . "'";

		$this->_conn->query($query);

	    return true;
	}

	public function GarbageCollector($maxLifeTime = null)
	{
		if (is_set($maxLifeTime) == false)
		{
			$maxLifeTime = $this->_timeout;
		}

	    $timeout = time() - $maxLifeTime;

	    $query = "	DELETE
	    			FROM 	sessionMaster
	    			WHERE 	lastaccess < {$timeout}";

		$this->_conn->query($query);

	    return  $this->_conn->affected_rows;
	}
}
?>