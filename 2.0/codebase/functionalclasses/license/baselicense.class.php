<?php
/**
 * Base License Class
 * 
 * @package Sandstone
 * @subpackage License
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */


class BaseLicense extends Module
{
	
	public static $CodeTablesDBconfig = Array(	"DBhost" => "localhost", 
												"DBuser" => "barracud_codetab",
												"DBpass" => "barracudasuite",
												"DBname" => "barracud_codetables");
												
	public static $LicenseDBconfig = Array(	"DBhost" => "localhost", 
											"DBuser" => "barracud_license",
											"DBpass" => "barracudasuite",
											"DBname" => "barracud_master");
											
	public function __construct($ID)
	{
		if (is_set($ID) && is_numeric($ID))
		{
			$this->LoadByID($ID);
		}
	}
	
	/**
	 * IsValid property
	 * 
	 * @return boolean
	 */
	public function getIsValid()
	{
		return true;
	}
	
	/**
	 * DBconfigArray property
	 * 
	 * @return array
	 */
	public function getDBconfigArray()
	{
		return null;
	}
	
	public function LoadByID($ID)
	{
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function getTemplateDirectory()
	{
		// Put the full path to your template directory here.
	}
	
	// For working in environments where the BaseURL cannot be computeted by COUNT("/")
	// We allow overwriting of this.
	//
	// Ex.  http://www.designinginteractive.com/~marsyste/Admin
	// Needs overwriting since it calculates to http://www.designinginteractive.com/~marsyste (without the Admin)
	public function getBaseURL()
	{
	}
	
	// Give a custom URL for the SSL version of the site
	public function getSecureURL()
	{
	}
	
	// Returns the full file spec for the root of the account
	// Needs overwriting on each account
	public function getAccountFileSpec()
	{
	}
		
}


?>