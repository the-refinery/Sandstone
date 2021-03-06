<?php
/**
 * Messages Class
 *
 * @package Sandstone
 * @subpackage Message
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

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

		if (is_set($this->_associatedEntityID) && $this->_associatedEntityID > 0)
		{
			$this->Load();
		}
	}

	/**
	 * AssociatedEntityType property
	 *
	 * @return string
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}

	/**
	 * AssociatedEntityID property
	 *
	 * @return int
	 *
	 * @param int $Value
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

	/**
	 * Messages property
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages;
	}

	/**
	 * LatestMessage property
	 *
	 * @return Message
	 */
	public function getLatestMessage()
	{
		return $this->_latestMessage;
	}

	public function Load()
	{

		$this->_messages = Array();

		$conn = GetConnection();

		$selectClause = Message::GenerateBaseSelectClause();
        $fromClause = Message::GenerateBaseFromClause();
        $whereClause = "	WHERE	a.AssociatedEntityType = {$conn->SetTextField($this->_associatedEntityType)}
        					AND		a.AssociatedEntityID = {$this->_associatedEntityID} ";
		$orderByClause = "	ORDER BY a.Timestamp DESC ";

        $query = $selectClause . $fromClause . $whereClause . $orderByClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow())
			{

				$tempMessage = new Message($dr);

				if ($tempMessage->IsLoaded)
				{
					$this->_messages[$tempMessage->MessageID] = $tempMessage;

					//The first message we get is the latest message
					if (is_set($this->_latestMessage) == false)
					{
						$this->_latestMessage = $tempMessage;
					}

					$returnValue = true;

				}

			}

		}
		else
		{
			$returnValue = false;
		}

		$this->_isLoaded = $returnValue;

		return $returnValue;

	}

	public function AddMessage($User, $Subject, $Content)
	{

		if ($User instanceof User && $User->IsLoaded && strlen($Subject) > 0 && strlen($Content) > 0)
		{
			$newMessage = new Message(null, $this->_associatedEntityType, $this->_associatedEntityID);
			$newMessage->User = $User;
			$newMessage->Subject = $Subject;
			$newMessage->Content = $Content;

			$returnValue = $newMessage->Save();

            if ($returnValue == true)
            {
            	//Add it to a new array, then copy all existing messages into it. to
            	//ensure they are in descending date order.
				$newArray[$newMessage->MessageID] = $newMessage;

				foreach($this->_messages as $tempMessage)
				{
					$newArray[$tempMessage->MessageID] = $tempMessage;
				}

                $this->_messages = $newArray;
            }

		}
        else
        {
            $returnValue = false;
        }

        return $returnValue;

	}

	public function RemoveMessage($Message)
	{
		if ($Message instanceof Message && $Message->IsLoaded)
		{
			if (array_key_exists($Message->MessageID, $this->_messages))
			{
				$messageID = $Message->MessageID;

				$this->_messages[$messageID ]->Delete();
				unset($this->_messages[$messageID]);

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
