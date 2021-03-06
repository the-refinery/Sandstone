<?php
/*
Messages Class

@package Sandstone
@subpackage Message
*/

SandstoneNamespace::Using("Sandstone.Database");

class Messages extends Module
{

	protected $_associatedEntityType;
	protected $_associatedEntityID;

	protected $_messages;

	protected $_latestMessage;

	public function __construct($AssociatedEntityType, $AssociatedEntityID)
	{

		$this->_associatedEntityType = strtolower($AssociatedEntityType);
		$this->_associatedEntityID = $AssociatedEntityID;

		$this->_messages = new DIarray();

		if (is_set($this->_associatedEntityID) && $this->_associatedEntityID > 0)
		{
			$this->Load();
		}
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

	@param int $Value
	*/
	public function getAssociatedEntityID()
	{
		return $this->_associatedEntityID;
	}

	public function setAssociatedEntityID($Value)
	{
		if (is_numeric($Value))
		{
			$this->_associatedEntityID = $Value;
		}
	}

	/*
	Messages property

	@return array
	*/
	public function getMessages()
	{
		return $this->_messages;
	}

	/*
	LatestMessage property

	@return Message
	*/
	public function getLatestMessage()
	{
		return $this->_latestMessage;
	}

	public function Load()
	{

		$returnValue = false;

		$this->_latestMessage = null;

		$query = new Query();

		$selectClause = Message::GenerateBaseSelectClause();
        $fromClause = Message::GenerateBaseFromClause();
        $whereClause = "	WHERE	a.AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
        					AND		a.AssociatedEntityID = {$this->_associatedEntityID} ";
		$orderByClause = "	ORDER BY a.Timestamp DESC ";

        $query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause;

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$query->LoadEntityArray($this->_messages, "Message", "MessageID", $this, "LoadCallback");
			$returnValue = true;
		}

		$this->_isLoaded = $returnValue;

		return $returnValue;

	}

	public function LoadCallback($Message)
	{
		//The first message we get is the latest message
		if (is_set($this->_latestMessage) == false)
		{
			$this->_latestMessage = $Message;
		}
	}

	public function AddMessage($User, $Subject, $Content, $IsEmailOnComment = false)
	{

		$returnValue = false;

		if ($User instanceof User && $User->IsLoaded && strlen($Subject) > 0 && strlen($Content) > 0)
		{
			$newMessage = new Message();
			$newMessage->AssociatedEntityType = $this->_associatedEntityType;
			$newMessage->AssociatedEntityID = $this->_associatedEntityID;
			$newMessage->User = $User;
			$newMessage->Subject = $Subject;
			$newMessage->Content = $Content;
			$newMessage->IsEmailOnComment = $IsEmailOnComment;

			$returnValue = $newMessage->Save();

			$success = new DIarray();

            if ($success == true)
            {
            	$returnValue = $this->Load();
            }

		}

        return $returnValue;

	}

	public function RemoveMessage($Message)
	{
		$returnValue = false;

		if ($Message instanceof Message && $Message->IsLoaded)
		{
			if (array_key_exists($Message->MessageID, $this->_messages))
			{
				$messageID = $Message->MessageID;

				$this->_messages[$messageID ]->Delete();

				$returnValue = $this->Load();
			}
		}

		return $returnValue;
	}

	public function Clear()
	{
		foreach ($this->_messages as $tempMessage)
		{
			$tempMessage->Delete();
		}
		
		$this->_messages->Clear();
		
		return true;
	}

	public function CountUnreadMessages($User = null)
	{
		$returnValue = 0;
	
		if (is_set($User) == false)
		{
			$User = Application::CurrentUser();
		}
	
		if ($User instanceof User && $User->IsLoaded)
		{
			foreach ($this->_messages as $tempMessage)
			{
				if ($tempMessage->CheckReadStatus($User) == false)
				{
					$returnValue++;
				}
			}			
		}
			
		return $returnValue;
	}

	public function CountSearchTermOccurrances($SearchTerm)
	{

		$returnValue = 0;

		if (count($this->_messages) > 0)
		{
			foreach ($this->_messages as $tempMessage)
			{
				$returnValue += $tempMessage->CountSearchTermOccurrances($SearchTerm);
			}
		}

		return $returnValue;
	}
}
?>
