<?php
/**
 * Files Collective Class
 * 
 * @package Sandstone
 * @subpackage File
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

class Files extends Module
{
	
	protected $_associatedEntityModule;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_files = array();
	protected $_primaryImage;

	public function __construct($Module = null, $Type = null, $ID = null)
	{
		if (is_set($ID) && is_numeric($ID) && is_set($Type) && is_set($Module))
		{

			$this->_associatedEntityModule = $Module;
			$this->_associatedEntityType = $Type;
			$this->_associatedEntityID = $ID;
			
			$this->Load();
		}
	}

	/**
	 * AssociatedEntityModule property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityModule()
	{
		return $this->_associatedEntityModule;
	}
	
	public function setAssociatedEntityModule($Value)
	{
		$this->_associatedEntityModule = trim($Value);
	}
	
	/**
	 * AssociatedEntityType property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}
	
	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = trim($Value);
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
	 * Files property
	 * 
	 * @return array
	 */
	public function getFiles()
	{
		$returnValue = $this->_files;
		
		return $returnValue;
	}
	
	public function Load()
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "File";
		$idField = $this->_associatedEntityType . "ID";
		
		$conn = GetConnection();
		
		$query = "	SELECT 	a.FileID, 
							b.URL, 
							b.FileName, 
							b.Description,
							b.Version
					FROM 	$tableName a 
							INNER JOIN core_FileMaster b ON a.FileID = b.FileID 
					WHERE	a.$idField = {$this->_associatedEntityID}";

		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to 
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow()) 
			{
				
				$tempFile = new File($dr);
	
				if ($tempFile->IsLoaded)
				{
							
					$this->_files[$tempFile->FileID] = $tempFile;
						
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

	public function Save()
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "File";
		$idField = $this->_associatedEntityType . "ID";
		
		$conn = GetConnection();

		
		//First, clear all database entries
		$query = "DELETE FROM $tableName
					WHERE $idField = {$this->_associatedEntityID}";
		
		$conn->Execute($query);
		
		//Now loop through each of the images and add a record for each
		foreach ($this->_files as $tempFile)
		{
			
			$query = "	INSERT INTO $tableName
						(
							$idField,
							FileID
						)
						VALUES
						(
							{$this->_associatedEntityID},
							{$tempFile->FileID}
						)";
			
			$conn->Execute($query);
		}
		
		$this->_isLoaded = true;
		
	}
	
	public function AddFile($NewFile)
	{
		if ($NewFile instanceof File)
		{			
			//Add it to the array.
			$this->_files[$NewFile->FileID] = $NewFile;
		}
	}
	
	public function RemoveFile($OldFile)
	{
		if ($OldFile instanceof File)
		{
			//Clear the array element
			unset($this->_files[$OldFile->FileID]);
		}
	}
		
	public function Export()
	{	

		foreach($this->_files as $tempFile)
		{
			$this->_exportEntities[] = $tempFile->Export();
		}
		
		return parent::Export();
	}	
	
}

?>