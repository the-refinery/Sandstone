<?php
/*
Message Class

@package Sandstone
@subpackage Message
*/

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.User");

class Message extends Module
{

	protected $_messageID;
	protected $_associatedEntityType;
	protected $_associatedEntityID;

	protected $_user;
	protected $_timestamp;
	protected $_subject;
	protected $_content;

	protected $_comments;
    protected $_latestComment;

    public function __construct($ID = null, $AssociatedEntityType = null, $AssociatedEntityID = null)
    {
        if (is_set($ID))
        {
            if (is_array($ID))
            {
                $this->Load($ID);
            }
            else
            {
                $this->LoadByID($ID);
            }
        }
        else
        {
            //This is a new Message
            $this->_associatedEntityType = strtolower($AssociatedEntityType);
            $this->_associatedEntityID = $AssociatedEntityID;

            $this->_comments = Array();
        }
    }

	/*
	MessageID property

	@return int
	*/
	public function getMessageID()
	{
		return $this->_messageID;
	}

	/*
	AssociatedEntityType property

	@return string
	*/
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}

	/*
	AssociatedEntityID property

	@return int
	*/
	public function getAssociatedEntityID()
	{
		return $this->_associatedEntityID;
	}

	/*
	User property

	@return User
	@param User $Value
	*/
	public function getUser()
	{
		return $this->_user;
	}

	public function setUser($Value)
	{
		if ($Value instanceof User && $Value->IsLoaded)
		{
			$this->_user = $Value;
		}
		else
		{
			$this->_user = null;
		}

	}

	/*
	Timestamp property

	@return Date
	*/
	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	/*
	Subject property

	@return string
	@param string $Value
	*/
	public function getSubject()
	{
		return $this->_subject;
	}

	public function setSubject($Value)
	{
		$this->_subject = $Value;
	}

	/*
	Content property

	@return string
	@param string $Value
	*/
	public function getContent()
	{
		return $this->_content;
	}

	public function setContent($Value)
	{
		$this->_content = $Value;
	}

	/*
	Comments property

	@return Array
	*/
	public function getComments()
	{
		return $this->_comments;
	}

    /*
    LatestComment property

    @return Comment
    */
    public function getLatestComment()
    {
        return $this->_latestComment;
    }

    public function Load($dr)
    {

		$this->_messageID = $dr['MessageID'];
		$this->_associatedEntityType = $dr['AssociatedEntityType'];
		$this->_associatedEntityID = $dr['AssociatedEntityID'];

		$this->_user = new User($dr['UserID']);
		$this->_timestamp = new Date($dr['Timestamp']);
		$this->_subject = $dr['Subject'];
		$this->_content = $dr['Content'];

        $returnValue = $this->LoadComments();

        $this->_isLoaded = $returnValue;

        return $returnValue;
    }

    public function LoadByID($ID)
    {
        $conn = GetConnection();

        $selectClause = self::GenerateBaseSelectClause();
        $fromClause = self::GenerateBaseFromClause();
        $whereClause = "WHERE     MessageID = {$ID} ";

        $query = $selectClause . $fromClause . $whereClause;

        $ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
        {
            $dr = $ds->FetchRow();
            $returnValue = $this->Load($dr);
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;

    }

    protected function LoadComments()
    {

        $this->_comments = Array();

        $conn = GetConnection();

        $selectClause = MessageComment::GenerateBaseSelectClause();
        $fromClause = MessageComment::GenerateBaseFromClause();
        $whereClause = "WHERE     MessageID = {$this->_messageID} ";
        $orderByClause = "ORDER BY a.Timestamp ASC ";

        $query = $selectClause . $fromClause . $whereClause . $orderByClause;

        $ds = $conn->Execute($query);

        if ($ds)
        {
            if ($ds->RecordCount() > 0)
            {

                //Set the return value to failure, then set it to true as soon as we are able to
                //successfully load one.
                $returnValue = false;

                while ($dr = $ds->FetchRow())
                {

                    $tempComment = new MessageComment($dr);

                    if ($tempComment->IsLoaded)
                    {
                        $this->_comments[$tempComment->CommentID] = $tempComment;

                        $returnValue = true;
                    }

                }

                //Save the last one as our Latest Comment
                $this->_latestComment = $tempComment;

            }
            else
            {
                //Return True if there weren't any records,
                //since it's ok for a message to not have any comments
                $returnValue = true;
            }

        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;
    }

	public function Save()
	{

		$isOkToSave = $this->ValidateRequiredProperties();

		if ($isOkToSave == true)
		{

			if (is_set($this->_messageID) OR $this->_messageID > 0)
			{
				$returnValue = $this->SaveUpdateRecord();
			}
			else
			{
				$returnValue = $this->SaveNewRecord();
			}

		}
		else
		{
			$returnValue = false;
		}

		$this->_isLoaded = $returnValue;

		return $returnValue;

	}

	protected function SaveNewRecord()
	{

		$conn = GetConnection();

		$accountID = Application::License()->AccountID;

		$query = "	INSERT INTO core_MessageMaster
					(
						AccountID,
						AssociatedEntityType,
						AssociatedEntityID,
						UserID,
						Timestamp,
						Subject,
						Content
					)
					VALUES
					(
						{$accountID},
						{$conn->SetTextField($this->_associatedEntityType)},
						{$this->_associatedEntityID},
						{$this->_user->UserID},
						NOW(),
						{$conn->SetTextField($this->_subject)},
						{$conn->SetTextField($this->_content)}
					)";

		$conn->Execute($query);


		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_messageID = $dr['newID'];

		$returnValue = $this->RefreshTimestamp();

		return $returnValue;

	}

	protected function SaveUpdateRecord()
	{

		$conn = GetConnection();

		$query = "	UPDATE core_MessageMaster SET
						UserID = {$this->_user->UserID},
						Subject = {$conn->SetTextField($this->_subject)},
						Content = {$conn->SetTextField($this->_content)}
					WHERE MessageID = {$this->_messageID}";

		$conn->Execute($query);

		return true;

	}

	protected function ValidateRequiredProperties()
	{

		//Start as true, and set it to false if something is missing
		$returnValue = true;

		if (strlen($this->_associatedEntityType) == 0)
		{
			$returnValue = false;
		}

		if (is_set($this->_associatedEntityID) == false)
		{
			$returnValue = false;
		}

		if (is_set($this->_user) == false)
		{
			$returnValue = false;
		}

		if (strlen($this->_subject) == 0)
		{
			$returnValue = false;
		}

		if (strlen($this->_content) == 0)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function RefreshTimestamp()
	{
		$conn = GetConnection();

		$query = "	SELECT	Timestamp
					FROM    core_MessageMaster
					WHERE	MessageID = {$this->_messageID} ";

		$ds = $conn->Execute($query);

        if ($ds && $ds->RecordCount() > 0)
        {
            $dr = $ds->FetchRow();
            $this->_timestamp = new Date($dr['Timestamp']);

            $returnValue = true;
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;

	}

	public function Delete()
	{

		//First delete any comments
		if (count($this->_comments) > 0)
		{
			foreach($this->_comments as $tempComment);
			{
				$tempComment->Delete();
			}

			$this->_comments = Array();
		}

		//Now delete this record.
		$conn = GetConnection();

		$query = "	DELETE
				    FROM    core_MessageMaster
				    WHERE MessageID = {$this->_messageID} ";

		$conn->Execute($query);

		$this->_messageID = null;

		return true;
	}

    public function AddComment($User, $Content)
    {
        if ($User instanceof User && $User->IsLoaded && strlen($Content) > 0)
        {
            $newComment = new MessageComment(null, $this);
            $newComment->User = $User;
            $newComment->Content = $Content;

            $returnValue = $newComment->Save();

            if ($returnValue == true)
            {
                $this->_comments[$newComment->CommentID] = $newComment;
                $this->_latestComment = $newComment;
            }
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;
    }

	public function RemoveComment($Comment)
	{
		if ($Comment instanceof MessageComment && $Comment->IsLoaded)
		{
			if (array_key_exists($Comment->CommentID, $this->_comments))
			{
				$commentID = $Comment->CommentID;

				$this->_comments[$commentID]->Delete();
				unset($this->_comments[$commentID]);

				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function CountSearchTermOccurrances($SearchTerm)
	{

		//First check my Subject
		$returnValue = substr_count($this->_subject, $SearchTerm);

		//Next my content
		$returnValue += substr_count($this->_content, $SearchTerm);

		//Now the content of any comments
		if (count($this->_comments) > 0)
		{
			foreach ($this->_comments as $tempComment)
			{
				$returnValue += substr_count($tempComment->Content, $SearchTerm);
			}
		}

		return $returnValue;
	}


    /*
    Static Query Functions
    */
    static public function GenerateBaseSelectClause()
    {
        $returnValue = "    SELECT	a.MessageID,
                                    a.AssociatedEntityType,
                                    a.AssociatedEntityID,
                                    a.UserID,
                                    a.Timestamp,
                                    a.Subject,
                                    a.Content ";

        return $returnValue;

    }

    static public function GenerateBaseFromClause()
    {
        $returnValue = "    FROM    core_MessageMaster a ";

        return $returnValue;
    }


}
?>
