<?php
/**
 * Action Class File
 * @package Sandstone
 * @subpackage Action
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

SandstoneNamespace::Using("Sandstone.ADOdb");
SandstoneNamespace::Using("Sandstone.Email");
SandstoneNamespace::Using("Sandstone.Email.Message");

class Action extends EntityBase
{

	protected $_loggingChanged;
	protected $_notificationsChanged;

	protected function SetupProperties()
	{
		$this->AddProperty("ActionID","integer","ActionID",true,false,true,false,false,null);
		$this->AddProperty("Name","string","Name",false,true,false,false,false,null);
		$this->AddProperty("Description","string","Description",false,false,false,false,false,null);
		$this->AddProperty("AssociatedPageName","string","AssociatedPageName",false,false,false,false,false,null);
		$this->AddProperty("AssociatedEntityType","string","AssociatedEntityType",false,false,false,false,false,null);
		$this->AddProperty("Logging","array",null,true,false,false,false,true,"LoadLogging");
		$this->AddProperty("Notifications","array",null,true,false,false,false,true,"LoadNotifications");

		parent::SetupProperties();
	}

	public function Save()
	{
		$returnValue = parent::Save();

		if ($returnValue == true && $this->_loggingChanged == true)
		{
			$returnValue = $this->SaveLogging();
		}

		if ($returnValue == true && $this->_notificationsChanged == true)
		{
			$returnValue = $this->SaveNotifications();
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_ActionMaster
							(
								Name,
								Description,
								AssociatedPageName,
								AssociatedEntityType
							)
							VALUES
							(
								{$conn->SetTextField($this->_name)},
								{$conn->SetNullTextField($this->_description)},
								{$conn->SetNullTextField($this->_associatedPageName)},
								{$conn->SetNullTextField($this->_associatedEntityType)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$conn = GetConnection();

		$query = "	UPDATE core_ActionMaster SET
								Name = {$conn->SetTextField($this->_name)},
								Description = {$conn->SetNullTextField($this->_description)},
								AssociatedPageName = {$conn->SetNullTextField($this->_associatedPageName)},
								AssociatedEntityType = {$conn->SetNullTextField($this->_associatedEntityType)}
							WHERE ActionID = {$this->_actionID}";

		$conn->Execute($query);

		return true;
	}

	public function LoadLogging()
	{

		$this->_logging->Clear();

		$conn = GetConnection();

		$selectClause = ActionLogging::GenerateBaseSelectClause();

		$fromClause = ActionLogging::GenerateBaseFromClause();

		$whereClause = "WHERE	a.ActionID = {$this->_actionID}";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds)
		{

			while ($dr = $ds->FetchRow())
			{
				$tempLogging = new ActionLogging($dr);

				$this->_logging[$tempLogging->AssociatedEntityID] = $tempLogging;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function EnableFullLogging()
	{

		//Remove any other logging
		$this->_logging->Clear();

		//Ad a new logging object
		$this->_logging[0] = new ActionLogging($this->_actionID);

		$this->_loggingChanged = true;

	}

	public function AddLoggedEntityID($AssociatedEntityID)
	{

		//Make sure all current settings are loaded
		if (count($this->_logging) == 0)
		{
			$this->LoadLogging();
		}


		if (is_numeric($AssociatedEntityID) && $AssociatedEntityID > 0)
		{
			//Ad a new logging object
			$tempLogging = new ActionLogging($this-_actionID);
			$tempLogging->AssociatedEntityID = $AssociatedEntityID;

			$this->_logging[$AssociatedEntityID] = $tempLogging;

			//Remove the overall one if it's set
			unset($this->_logging[0]);

			$this->_loggingChanged = true;
		}

	}

	public function RemoveLoggedEntityID($AssociatedEntityID)
	{

		if (count($this->_logging) == 0)
		{
			$this->LoadLogging();
		}

		if (is_numeric($AssociatedEntityID) && $AssociatedEntityID > 0)
		{
			unset($this->_logging[$AssociatedEntityID]);

			$this->_loggingChanged = true;
		}

	}

	public function DisableAllLogging()
	{
		//Remove any logging
		$this->_logging->Clear();

		$this->_loggingChanged = true;

	}

	protected function SaveLogging()
	{
		$conn = GetConnection();

		//Clear any existing Records
		$query = "	DELETE
					FROM	core_ActionLogging
					WHERE	ActionID = {$this->_actionID}";

		$conn->Execute($query);

		//Now insert any new records
		if (count($this->_logging) > 0)
		{
			foreach ($this->_logging as $tempLogging)
			{
				$tempLogging->Save();
			}
		}

		return true;

	}

	public function LoadNotifications()
	{
		$this->_notifications->Clear();

		$conn = GetConnection();

		$selectClause = ActionNotification::GenerateBaseSelectClause();

		$fromClause = ActionNotification::GenerateBaseFromClause();

		$whereClause = "WHERE	a.ActionID = {$this->_actionID}";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds)
		{

			while ($dr = $ds->FetchRow())
			{

				if (array_key_exists($dr['AssociatedEntityID'], $this->_notifications))
				{
					//We already have something for this entity ID, add the additional email
					$this->_notifications[$dr['AssociatedEntityID']]->AddEmail($dr['EmailID']);
				}
				else
				{
					//This is a new entity id
					$tempNotification = new ActionNotification($dr);

					$this->_notifications[$tempNotification->AssociatedEntityID] = $tempNotification;
				}
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	public function AddNotification($Email, $AssociatedEntityID = 0)
	{

		if (count($this->_notifications) == 0)
		{
			$this->LoadNotifications();
		}

		if ($Email instanceof Email && $Email->IsLoaded)
		{
			if (array_key_exists($AssociatedEntityID, $this->_notifications))
			{
				//We already have something for this entity ID, add the additional email
				$this->_notifications[$AssociatedEntityID]->AddEmail($Email->EmailID);
			}
			else
			{
				//This is a new entity id
				$tempNotification = new ActionNotification($this->_actionID);
				$tempNotification->AssociatedEntityID = $AssociatedEntityID;
				$tempNotification->AddEmail($Email->EmailID);

				$this->_notifications[$tempNotification->AssociatedEntityID] = $tempNotification;
			}

			$this->_notificationsChanged = true;
		}
	}

	public function RemoveNotification($Email, $AssociatedEntityID = 0)
	{

		if (count($this->_notifications) == 0)
		{
			$this->LoadNotifications();
		}

		if ($Email instanceof Email && $Email->IsLoaded)
		{
			if (array_key_exists($AssociatedEntityID, $this->_notifications))
			{
				$this->_notifications[$AssociatedEntityID]->RemoveEmail($Email->EmailID);

				$this->_notificationsChanged = true;
			}
		}

	}

	public function ClearNotifications()
	{

		$this->_notifications->Clear();

		$this->_notificationsChanged = true;

	}

	protected function SaveNotifications()
	{
		$conn = GetConnection();

		//Clear any existing Records
		$query = "	DELETE
					FROM	core_ActionNotification
					WHERE	ActionID = {$this->_actionID}";

		$conn->Execute($query);

		//Now insert any new records
		if (count($this->_notifications) > 0)
		{
			foreach ($this->_notifications as $tempNotification)
			{
				$tempNotification->Save();
			}
		}

		return true;

	}

	public function LogHistory($Details, $AssociatedEntityID = null, $AssociatedPageName = null, $AssociatedEntityType = null)
	{

		$isLogged = $this->CheckLogging($AssociatedEntityID);

		if ($isLogged)
		{
			$conn = GetConnection();

			//Actions are always assigned to the current logged in user.
			$tempUserID = Application::CurrentUser()->UserID;

			$query = "	INSERT INTO core_ActionHistory
						(
							ActionID,
							Timestamp,
							AssociatedEntityID,
							Details,
							UserID,
							AssociatedPageName,
							AssociatedEntityType
						)
						VALUES
						(
							{$this->_actionID},
							NOW(),
							{$conn->SetNullNumericField($AssociatedEntityID)},
							{$conn->SetTextField($Details)},
							{$conn->SetNullNumericField($tempUserID)},
							{$conn->SetNullTextField($AssociatedPageName)},
							{$conn->SetNullTextField($AssociatedEntityType)}
						)";

			$conn->Execute($query);

			$returnValue = true;

		}
		else
		{
			$returnValue = true;
		}


		//Now handle any notifications
		$this->ProcessNotifications($AssociatedEntityID, $Details);

		return $returnValue;
	}

	protected function CheckLogging($AssociatedEntityID)
	{

		if (count($this->_logging) == 0)
		{
			$this->LoadLogging();
		}

		//First check for overall logging
		if (array_key_exists(0, $this->_logging))
		{
			$returnValue = true;
		}
		else
		{
			if (is_set($AssociatedEntityID))
			{
				if (array_key_exists($AssociatedEntityID, $this->_logging))
				{
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

		}

		return $returnValue;
	}

	protected function ProcessNotifications($AssociatedEntityID, $Details)
	{

		$targetEmails= new DIarray();

		if (count($this->_notifications) == 0)
		{
			$this->LoadNotifications();
		}

		//Handle the overall notifications
		if (array_key_exists(0, $this->_notifications))
		{
			foreach($this->_notifications[0]->Emails as $tempEmail)
			{
				$targetEmails[$tempEmail->EmailID] = $tempEmail;
			}
		}

		//Handle the notifications for this specific EntityID (if any)
		if (is_set($AssociatedEntityID))
		{
			if (array_key_exists($AssociatedEntityID, $this->_notifications))
			{
				foreach($this->_notifications[$AssociatedEntityID]->Emails as $tempEmail)
				{
					$targetEmails[$tempEmail->EmailID] = $tempEmail;
				}

			}
		}

		//Now send the messages
		$this->SendEmailNotifications($Details, $targetEmails);

	}

	protected function SendEmailNotifications($Details, $Emails)
	{

		$registry = Application::Registry();

		//Setup the standard From email
		$fromEmail = new Email();
		$fromEmail->Address = $registry->AdminEmail;

		foreach ($Emails as $tempEmail)
		{

			//Create a new message
			$notification = new EmailMessage();

	        //Set the To
	        $notification->AddRecipient("Test", $tempEmail);

			//Set the From
			$notification->FromDisplayName = "{$registry->SiteTitle} Notification System";
			$notification->FromEmail = $fromEmail;

			//Set the Subject
			$notification->Subject = "{$this->_description} Alert";

			//Set the message body
			$notification->Message = $Details;
			$notification->IsPreformatted = true;

			//Send the email
			$notification->Send();


		}

	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ActionID,
										a.Name,
										a.Description,
										a.AssociatedPageName,
										a.AssociatedEntityType ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ActionMaster a ";

		return $returnValue;
	}

	static public function Log($Name, $Details, $AssociatedEntityID = null, $AssociatedPageName = null, $AssociatedEntityType = null)
	{

		$targetAction = Action::LookupActionByName($Name);

		if (is_set($targetAction))
		{
			$returnValue = $targetAction->LogHistory($Details, $AssociatedEntityID, $AssociatedPageName, $AssociatedEntityType);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	static public function LookupActionByName($Name)
	{
		if (strlen($Name) > 0)
		{
			$searchName = strtolower($Name);

			$conn = GetConnection();

			$selectClause = Action::GenerateBaseSelectClause();
			$fromClause = Action::GenerateBaseFromClause();
			$whereClause = "WHERE LOWER(a.Name) = '{$searchName}'";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds && $ds->RecordCount() > 0)
			{
				$dr = $ds->FetchRow();
				$returnValue = new Action($dr);
			}
			else
			{
				$returnValue = null;
			}

		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;

	}

}
?>