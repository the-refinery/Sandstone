<?php
/**
 * Traffic Class File
 * 
 * @package Sandstone
 * @subpackage Traffic
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Traffic extends Module
	{
		protected $_ipAddress;
		protected $_userID;
		protected $_URL;
		protected $_referer;
		protected $_userAgent = "";
		protected $_operatingSystem = ""; 
		protected $_operatingSystemVersion = ""; 
		protected $_browser = "" ;
		protected $_browserVersion = ""; 
		protected $_resolved = false; // resolving proceeded
		protected $_type = ""; // browser/Robot
		protected $_languages;
		protected $_seoPage;
		
		public function __construct($userAgent = null, $cron = false)
		{
			if ($cron == false)
			{
				$this->_userAgent = $userAgent;
				$this->Resolve();
			}
		}

		public function setUserID($Value)
		{
			$this->_userID = $Value;	
		}
		
		public function setSEOPage($Value)
		{
			$this->_seoPage = $Value;
		}
		
		public function Resolve()
		{
			global $LICENSE;
			
			$this->_resolved = false;
			$this->_operatingSystem = "";
			$this->_operatingSystemVersion = "";
			
			$this->_ipAddress = $_SERVER['REMOTE_ADDR'];
			$this->_URL = strtolower("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			
			if (! (eregi("barracudasuite.com", $_SERVER['HTTP_REFERER']) || eregi($LICENSE->URL, $_SERVER['HTTP_REFERER'])))
			{
				$this->_referer = $_SERVER['HTTP_REFERER'];
			}
			
			$this->_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			
			$this->FindOperatingSystem();
			$this->FindBrowser();
			
			$this->_resolved = true;
		}
		
		public function LogVisit()
		{
			$conn = GetConnection();

			$query = "	INSERT INTO core_TrafficPageViewLog
						(
							 IP,
							 Timestamp,
							 UserID,
							 URL,
							 ReferrerURL,
							 Browser,
							 BrowserVersion,
							 OperatingSystem,
							 OperatingSystemVersion,
							 Language, 
							 SEOpageID  
						)
						VALUES
						(
							{$conn->SetTextField($this->_ipAddress)},
							Now(),
							{$conn->SetNullNumericField($this->_userID)},
							{$conn->SetTextField($this->_URL)},
							{$conn->SetNullTextField($this->_referer)},
							{$conn->SetNullTextField($this->_browser)},
							{$conn->SetNullTextField($this->_browserVersion)},
							{$conn->SetNullTextField($this->_operatingSystem)},
							{$conn->SetNullTextField($this->_operatingSystemVersion)},
							{$conn->SetNullTextField($this->_languages)},
							{$conn->SetNullNumericField($this->_seoPage->SEOPageID)} 
						)";

			$conn->Execute($query);
		}

		/**
		 * Copies data in our raw log (core_TrafficPageViewLog) into the History (core_TrafficPageViewHistory)
		 *
		 */
		public function CopyDailyToFullHistory()
		{
			$today = new Date(date('m/d/Y'));
			
			$conn = GetConnection();
			
			$query = "	
			INSERT INTO core_TrafficPageViewHistory 
			(SELECT 
					PageViewID, 
					IP,
					 `Timestamp`,
					 UserID,
					 URL,
					 ReferrerURL,
					 Browser,
					 BrowserVersion,
					 OperatingSystem,
					 OperatingSystemVersion,
					 Language, 
					 SEOpageID  
				FROM core_TrafficPageViewLog
				WHERE `Timestamp` < '" . $today->MySQLtimestamp . "')";
			
			$conn->Execute($query);
			
		}
		
		public function CreateDailySummary()
		{
			$today = new Date(date('m/d/Y'));
			$yesterday = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))));
			
			$conn = GetConnection();
			
			// SEO PAGE
			$query[] = "
				INSERT INTO core_TrafficByDaySEOpage	
				SELECT 	'" . $yesterday->MySQLtimestamp . "', 
						SEOpageID,  
						Count(DISTINCT IP) UniquePageView,
						Count(*) TotalPageView   
				FROM 	core_TrafficPageViewLog 
				WHERE 	Timestamp < '" . $today->MySQLtimestamp . "'
				AND 	SEOpageID IS NOT NULL
				GROUP BY '" . $yesterday->MySQLtimestamp . "', SEOpageID";
			
			// User
			$query[] = "
				INSERT INTO core_TrafficByDayUser	
				SELECT 	'" . $yesterday->MySQLtimestamp . "' `Date`, 
						UserID, 
						Count(DISTINCT IP) UniquePageView,
						Count(*) TotalPageView 
				FROM 	core_TrafficPageViewLog
				WHERE 	Timestamp < '" . $today->MySQLtimestamp . "' 
				AND 	UserID IS NOT NULL 
				GROUP BY '" . $yesterday->MySQLtimestamp . "', UserID";
			
			// Browser
			$query[] = "
				INSERT INTO core_TrafficByDayBrowser	
				SELECT 	'" . $yesterday->MySQLtimestamp . "', 
						Browser, 
						BrowserVersion, 
						Count(DISTINCT IP) UniquePageView,
						Count(*) TotalPageView 
				FROM 	`core_TrafficPageViewLog` 
				WHERE 	Timestamp < '" . $today->MySQLtimestamp . "' 
				AND 	Browser IS NOT NULL 
				AND		BrowserVersion IS NOT NULL 
				GROUP BY '" . $yesterday->MySQLtimestamp . "', Browser, BrowserVersion";
			
			// Operating System
			$query[] = "
				INSERT INTO core_TrafficByDayOS	
				SELECT 	'" . $yesterday->MySQLtimestamp . "', 
						OperatingSystem, 
						OperatingSystemVersion, 
						Count(DISTINCT IP) UniquePageView,
						Count(*) TotalPageView   
				FROM 	`core_TrafficPageViewLog` 
				WHERE 	Timestamp < '" . $today->MySQLtimestamp . "' 
				AND 	OperatingSystem IS NOT NULL 
				AND 	OperatingSystem IS NOT NULL
				GROUP BY '" . $yesterday->MySQLtimestamp . "', OperatingSystem, OperatingSystemVersion";
			
			// Language
			$query[] = "
				INSERT INTO core_TrafficByDayLanguage 
				SELECT 	'" . $yesterday->MySQLtimestamp . "', 
						Language, 
						Count(DISTINCT IP) UniquePageView, 
						Count(*) TotalPageView 
				FROM 	`core_TrafficPageViewLog` 
				WHERE 	Timestamp < '" . $today->MySQLtimestamp . "' 
				AND 	Language IS NOT NULL 
				GROUP BY '" . $yesterday->MySQLtimestamp . "', Language";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		public function CreateMonthlySummary()
		{
			$beginmonth = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") - 1, '01', date("Y"))));
			
			$conn = GetConnection();
			
			// Language
			$query[] = "
				INSERT INTO core_TrafficByMonthLanguage 
				SELECT 
					'" . $beginmonth->MySQLtimestamp . "', 
					Name,  
					SUM( TotalPageView ) TotalPageView,
					SUM( UniquePageView ) UniquePageView
				FROM core_TrafficByDayLanguage
				WHERE Date >= '" . $beginmonth->MySQLtimestamp . "'
				GROUP BY Name";
			
			// Browser
			$query[] = "
				INSERT INTO core_TrafficByMonthBrowser 
				SELECT 
					'" . $beginmonth->MySQLtimestamp . "', 
					Name, 
					Version, 
					SUM( TotalPageView ) TotalPageView,
					SUM( UniquePageView ) UniquePageView 
				FROM core_TrafficByDayBrowser
				WHERE Date >= '" . $beginmonth->MySQLtimestamp . "'
				GROUP BY Name, Version";
			
			// OS
			$query[] = "
				INSERT INTO core_TrafficByMonthOS 
				SELECT 
					'" . $beginmonth->MySQLtimestamp . "', 
					Name, 
					Version, 
					SUM( TotalPageView ) TotalPageView,
					SUM( UniquePageView ) UniquePageView 
				FROM core_TrafficByDayOS
				WHERE Date >= '" . $beginmonth->MySQLtimestamp . "'
				GROUP BY Name, Version";
			
			// SEOpageID
			$query[] = "
				INSERT INTO core_TrafficByMonthSEOpage 
				SELECT 
					'" . $beginmonth->MySQLtimestamp . "', 
					SEOpageID, 
					SUM( TotalPageView ) TotalPageView,
					SUM( UniquePageView ) UniquePageView 
				FROM core_TrafficByDaySEOpage
				WHERE Date >= '" . $beginmonth->MySQLtimestamp . "'
				GROUP BY Name";
			
			// User
			$query[] = "
				INSERT INTO core_TrafficByMonthUser 
				SELECT 
					'" . $beginmonth->MySQLtimestamp . "', 
					UserID, 
					SUM( TotalPageView ) TotalPageView,
					SUM( UniquePageView ) UniquePageView
				FROM core_TrafficByDayUser
				WHERE Date >= '" . $beginmonth->MySQLtimestamp . "'
				GROUP BY Name";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		public function CreateAnnualSummary()
		{
			$beginyear = new Date(date('m/d/Y', mktime(0, 0, 0, '01', '01', date("Y"))));
			
			$conn = GetConnection();
			
			// Language
			$query[] = "
				INSERT INTO core_TrafficByYearLanguage 
				SELECT 
					'" . $beginyear->MySQLtimestamp . "', 
					Name, 
					SUM( UniquePageView ) UniquePageView, 
					SUM( TotalPageView ) TotalPageView
				FROM core_TrafficByMonthLanguage
				WHERE Date >= '" . $beginyear->MySQLtimestamp . "'
				GROUP BY Name";
			
			// Browser
			$query[] = "
				INSERT INTO core_TrafficByYearBrowser 
				SELECT 
					'" . $beginyear->MySQLtimestamp . "', 
					Name, 
					Version, 
					SUM( UniquePageView ) UniquePageView, 
					SUM( TotalPageView ) TotalPageView
				FROM core_TrafficByMonthBrowser
				WHERE Date >= '" . $beginyear->MySQLtimestamp . "'
				GROUP BY Name, Version";
			
			// OS
			$query[] = "
				INSERT INTO core_TrafficByYearOS 
				SELECT 
					'" . $beginyear->MySQLtimestamp . "', 
					Name, 
					Version, 
					SUM( UniquePageView ) UniquePageView, 
					SUM( TotalPageView ) TotalPageView
				FROM core_TrafficByMonthOS
				WHERE Date >= '" . $beginyear->MySQLtimestamp . "'
				GROUP BY Name, Version";
			
			// SEOpageID
			$query[] = "
				INSERT INTO core_TrafficByYearSEOpage 
				SELECT 
					'" . $beginyear->MySQLtimestamp . "', 
					SEOpageID, 
					SUM( UniquePageView ) UniquePageView, 
					SUM( TotalPageView ) TotalPageView
				FROM core_TrafficByMonthSEOpage
				WHERE Date >= '" . $beginyear->MySQLtimestamp . "'
				GROUP BY Name";
			
			// User
			$query[] = "
				INSERT INTO core_TrafficByYearUser 
				SELECT 
					'" . $beginyear->MySQLtimestamp . "', 
					UserID, 
					SUM( UniquePageView ) UniquePageView, 
					SUM( TotalPageView ) TotalPageView
				FROM core_TrafficByMonthUser
				WHERE Date >= '" . $beginyear->MySQLtimestamp . "'
				GROUP BY Name";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Takes yesterday's daily log, and updates the current month to date listings.
		 *
		 */
		public function UpdateMonthToDate()
		{
			$yesterday = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))));
			
			$conn = GetConnection();
			
			// SEO PAGE
			$query[] = "
				UPDATE core_TrafficMTDSEOpage
				INNER JOIN 	core_TrafficByDaySEOpage ON core_TrafficByDaySEOpage.SEOpageID = core_TrafficMTDSEOpage.SEOpageID
				SET 		core_TrafficMTDSEOpage.TotalPageView = core_TrafficMTDSEOpage.TotalPageView + core_TrafficByDaySEOpage.TotalPageView,
							core_TrafficMTDSEOpage.UniquePageView = core_TrafficMTDSEOpage.UniquePageView + core_TrafficByDaySEOpage.UniquePageView
				WHERE 		core_TrafficByDaySEOpage.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficMTDSEOpage
				SELECT 	SEOpageID,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDaySEOpage
				WHERE 	SEOpageID NOT IN (SELECT SEOpageID FROM core_TrafficMTDSEOpage)
				AND 	core_TrafficByDaySEOpage.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// User
			$query[] = "
				UPDATE core_TrafficMTDUser
				INNER JOIN 	core_TrafficByDayUser ON core_TrafficByDayUser.UserID = core_TrafficMTDUser.UserID
				SET 		core_TrafficMTDUser.TotalPageView = core_TrafficMTDUser.TotalPageView + core_TrafficByDayUser.TotalPageView,
							core_TrafficMTDUser.UniquePageView = core_TrafficMTDUser.UniquePageView + core_TrafficByDayUser.UniquePageView
				WHERE 		core_TrafficByDayUser.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficMTDUser
				SELECT 	UserID,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayUser
				WHERE 	UserID NOT IN (SELECT UserID FROM core_TrafficMTDUser)
				AND 	core_TrafficByDayUser.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Language
			$query[] = "
				UPDATE core_TrafficMTDLanguage
				INNER JOIN 	core_TrafficByDayLanguage ON core_TrafficByDayLanguage.Name = core_TrafficMTDLanguage.Name
				SET 		core_TrafficMTDLanguage.TotalPageView = core_TrafficMTDLanguage.TotalPageView + core_TrafficByDayLanguage.TotalPageView,
							core_TrafficMTDLanguage.UniquePageView = core_TrafficMTDLanguage.UniquePageView + core_TrafficByDayLanguage.UniquePageView
				WHERE 		core_TrafficByDayLanguage.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficMTDLanguage
				SELECT 	Name,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayLanguage
				WHERE 	Name NOT IN (SELECT Name FROM core_TrafficMTDLanguage)
				AND 	core_TrafficByDayLanguage.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Browser
			$query[] = "
				UPDATE core_TrafficMTDBrowser
				INNER JOIN 	core_TrafficByDayBrowser ON 
								core_TrafficByDayBrowser.Name = core_TrafficMTDBrowser.Name
								AND core_TrafficByDayBrowser.Version = core_TrafficMTDBrowser.Version
				SET 	core_TrafficMTDBrowser.TotalPageView = core_TrafficMTDBrowser.TotalPageView + core_TrafficByDayBrowser.TotalPageView,
						core_TrafficMTDBrowser.UniquePageView = core_TrafficMTDBrowser.UniquePageView + core_TrafficByDayBrowser.UniquePageView
				WHERE 	core_TrafficByDayBrowser.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficMTDBrowser
				SELECT 	Name, 
						Version, 
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayBrowser
				WHERE 	Name NOT IN (	SELECT 	Name
										FROM 	core_TrafficMTDBrowser
										WHERE 	Version = core_TrafficByDayBrowser.Version)
				AND 	core_TrafficByDayBrowser.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Operating System
			$query[] = "
				UPDATE core_TrafficMTDOS
				INNER JOIN 	core_TrafficByDayOS ON 
								core_TrafficByDayOS.Name = core_TrafficMTDOS.Name
								AND core_TrafficByDayOS.Version = core_TrafficMTDOS.Version
				SET 	core_TrafficMTDOS.TotalPageView = core_TrafficMTDOS.TotalPageView + core_TrafficByDayOS.TotalPageView,
						core_TrafficMTDOS.UniquePageView = core_TrafficMTDOS.UniquePageView + core_TrafficByDayOS.UniquePageView
				WHERE 	core_TrafficByDayOS.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficMTDOS
				SELECT 	Name, 
						Version, 
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayOS
				WHERE 	Name NOT IN (	SELECT 	Name
										FROM 	core_TrafficMTDOS
										WHERE 	Version = core_TrafficByDayOS.Version)
				AND 	core_TrafficByDayOS.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Takes yesterday's daily log, and updates the current year to date listings.
		 *
		 */
		public function UpdateYearToDate()
		{
			$yesterday = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))));
			
			$conn = GetConnection();
			
			// SEO PAGE
			$query[] = "
				UPDATE core_TrafficYTDSEOpage
				INNER JOIN 	core_TrafficByDaySEOpage ON core_TrafficByDaySEOpage.SEOpageID = core_TrafficYTDSEOpage.SEOpageID
				SET 		core_TrafficYTDSEOpage.TotalPageView = core_TrafficYTDSEOpage.TotalPageView + core_TrafficByDaySEOpage.TotalPageView,
							core_TrafficYTDSEOpage.UniquePageView = core_TrafficYTDSEOpage.UniquePageView + core_TrafficByDaySEOpage.UniquePageView
				WHERE 		core_TrafficByDaySEOpage.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficYTDSEOpage
				SELECT 	SEOpageID,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDaySEOpage
				WHERE 	SEOpageID NOT IN (SELECT SEOpageID FROM core_TrafficYTDSEOpage)
				AND 	core_TrafficByDaySEOpage.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// User
			$query[] = "
				UPDATE core_TrafficYTDUser
				INNER JOIN 	core_TrafficByDayUser ON core_TrafficByDayUser.UserID = core_TrafficYTDUser.UserID
				SET 		core_TrafficYTDUser.TotalPageView = core_TrafficYTDUser.TotalPageView + core_TrafficByDayUser.TotalPageView,
							core_TrafficYTDUser.UniquePageView = core_TrafficYTDUser.UniquePageView + core_TrafficByDayUser.UniquePageView
				WHERE 		core_TrafficByDayUser.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficYTDUser
				SELECT 	UserID,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayUser
				WHERE 	UserID NOT IN (SELECT UserID FROM core_TrafficYTDUser)
				AND 	core_TrafficByDayUser.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Language
			$query[] = "
				UPDATE core_TrafficYTDLanguage
				INNER JOIN 	core_TrafficByDayLanguage ON core_TrafficByDayLanguage.Name = core_TrafficYTDLanguage.Name
				SET 		core_TrafficYTDLanguage.TotalPageView = core_TrafficYTDLanguage.TotalPageView + core_TrafficByDayLanguage.TotalPageView,
							core_TrafficYTDLanguage.UniquePageView = core_TrafficYTDLanguage.UniquePageView + core_TrafficByDayLanguage.UniquePageView
				WHERE 		core_TrafficByDayLanguage.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficYTDLanguage
				SELECT 	Name,
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayLanguage
				WHERE 	Name NOT IN (SELECT Name FROM core_TrafficYTDLanguage)
				AND 	core_TrafficByDayLanguage.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Browser
			$query[] = "
				UPDATE core_TrafficYTDBrowser
				INNER JOIN 	core_TrafficByDayBrowser ON 
								core_TrafficByDayBrowser.Name = core_TrafficYTDBrowser.Name
								AND core_TrafficByDayBrowser.Version = core_TrafficYTDBrowser.Version
				SET 	core_TrafficYTDBrowser.TotalPageView = core_TrafficYTDBrowser.TotalPageView + core_TrafficByDayBrowser.TotalPageView,
						core_TrafficYTDBrowser.UniquePageView = core_TrafficYTDBrowser.UniquePageView + core_TrafficByDayBrowser.UniquePageView
				WHERE 	core_TrafficByDayBrowser.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficYTDBrowser
				SELECT 	Name, 
						Version, 
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayBrowser
				WHERE 	Name NOT IN (	SELECT 	Name
										FROM 	core_TrafficYTDBrowser
										WHERE 	Version = core_TrafficByDayBrowser.Version)
				AND 	core_TrafficByDayBrowser.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			// Operating System
			$query[] = "
				UPDATE core_TrafficYTDOS
				INNER JOIN 	core_TrafficByDayOS ON 
								core_TrafficByDayOS.Name = core_TrafficYTDOS.Name
								AND core_TrafficByDayOS.Version = core_TrafficYTDOS.Version
				SET 	core_TrafficYTDOS.TotalPageView = core_TrafficYTDOS.TotalPageView + core_TrafficByDayOS.TotalPageView,
						core_TrafficYTDOS.UniquePageView = core_TrafficYTDOS.UniquePageView + core_TrafficByDayOS.UniquePageView
				WHERE 	core_TrafficByDayOS.Date = '" . $yesterday->MySQLtimestamp . "'";
			
			$query[] = "
				INSERT INTO core_TrafficYTDOS
				SELECT 	Name, 
						Version, 
						UniquePageView, 
						TotalPageView
				FROM 	core_TrafficByDayOS
				WHERE 	Name NOT IN (	SELECT 	Name
										FROM 	core_TrafficYTDOS
										WHERE 	Version = core_TrafficByDayOS.Version)
				AND 	core_TrafficByDayOS.Date = '" . $yesterday->MySQLtimestamp . "'
				";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Clear data out of current MTD
		 *
		 */
		public function ResetMonthToDate()
		{
			$conn = GetConnection();
			
			$query[] = "TRUNCATE TABLE core_TrafficMTDBrowser";
			$query[] = "TRUNCATE TABLE core_TrafficMTDLanguage";
			$query[] = "TRUNCATE TABLE core_TrafficMTDOS";
			$query[] = "TRUNCATE TABLE core_TrafficMTDSEOpage";
			$query[] = "TRUNCATE TABLEcore_TrafficMTDUser";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Clear data out of current YTD
		 *
		 */
		public function ResetYearToDate()
		{
			$conn = GetConnection();
			
			$query[] = "TRUNCATE TABLE core_TrafficYTDBrowser";
			$query[] = "TRUNCATE TABLE core_TrafficYTDLanguage";
			$query[] = "TRUNCATE TABLE core_TrafficYTDOS";
			$query[] = "TRUNCATE TABLE core_TrafficYTDSEOpage";
			$query[] = "TRUNCATE TABLE core_TrafficYTDUser";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Clears all records from yesterdays log, and leaves the ones for today intact
		 *
		 */
		public function CleanupDailyLog()
		{
			$today = new Date(date('m/d/Y'));
			
			$conn = GetConnection();
			
			$query = "DELETE 
				FROM core_TrafficPageViewLog
				WHERE `Timestamp` < '" . $today->MySQLtimestamp . "'";
			
			$conn->Execute($query);
		}
		
		/**
		 * Raw data is saved for a year.  Remove everything over 365 days ago
		 *
		 */
		public function CleanupFullHistory()
		{
			$cleanupdate = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") , date("d") - 365, date("Y"))));
			
			$conn = GetConnection();
			
			$query = "DELETE 
				FROM core_TrafficPageViewHistory
				WHERE `Timestamp` < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$conn->Execute($query);
			
		}
		
		/**
		 * Daily Summaries are saved for 13 months
		 *
		 */
		public function CleanupDailySummary()
		{
			$cleanupdate = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") - 13 , date("d"), date("Y"))));
			
			$conn = GetConnection();
			
			$query[] = "DELETE 
				FROM core_TrafficByDayBrowser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByDayLanguage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByDayOS
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByDaySEOpage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByDayUser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Monthly Summaries are saved for 25 months
		 *
		 */
		public function CleanupMonthlySummary()
		{
			$cleanupdate = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") - 25 , date("d"), date("Y"))));
			
			$conn = GetConnection();
			
			$query[] = "DELETE 
				FROM core_TrafficByMonthBrowser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByMonthLanguage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByMonthOS
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByMonthSEOpage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByMonthUser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		/**
		 * Annual Summaries are saved for 37 months
		 *
		 */
		public function CleanupAnnualSummary()
		{
			$cleanupdate = new Date(date('m/d/Y', mktime(0, 0, 0, date("m") - 37 , date("d"), date("Y"))));
			
			$conn = GetConnection();
			
			$query[] = "DELETE 
				FROM core_TrafficByYearBrowser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByYearLanguage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByYearOS
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByYearSEOpage
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			$query[] = "DELETE 
				FROM core_TrafficByYearUser
				WHERE Date < '" . $cleanupdate->MySQLtimestamp . "'";
			
			foreach ($query as $single)
			{
				$rs = $conn->Execute($single);
			}
		}
		
		public function FindOperatingSystem()
		{
			if (eregi("win", $this->_userAgent))
			{
				$this->_operatingSystem = "Windows";
				if ((eregi("Windows 95",$this->_userAgent)) || (eregi("Win95",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "95";
				}
				elseif (eregi("Windows ME",$this->_userAgent) || (eregi("Win 9x 4.90",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "ME";
				}
				elseif ((eregi("Windows 98",$this->_userAgent)) || (eregi("Win98",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "98";
				}
				elseif ((eregi("Windows NT 5.0",$this->_userAgent)) || (eregi("WinNT5.0",$this->_userAgent)) || (eregi("Windows 2000",$this->_userAgent)) || (eregi("Win2000",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "2000";
				}
				elseif ((eregi("Windows NT 5.1",$this->_userAgent)) || (eregi("WinNT5.1",$this->_userAgent)) || (eregi("Windows XP",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "XP";
				}
				elseif ((eregi("Windows NT 5.2",$this->_userAgent)) || (eregi("WinNT5.2",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = ".NET 2003";
				}
				elseif ((eregi("Windows NT 6.0",$this->_userAgent)) || (eregi("WinNT6.0",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "Codename: Longhorn";
				}
				elseif (eregi("Windows CE",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "CE";
				}
				elseif (eregi("Win3.11",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "3.11";
				}
				elseif (eregi("Win3.1",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "3.1";
				}
				elseif ((eregi("Windows NT",$this->_userAgent)) || (eregi("WinNT",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "NT";
				}
			}
			elseif (eregi("lindows",$this->_userAgent))
			{
				$this->_operatingSystem = "Lindows_operatingSystem";
			}
			elseif (eregi("mac",$this->_userAgent))
			{
				$this->_operatingSystem = "Macintosh";
				if ((eregi("Mac _operatingSystem X",$this->_userAgent)) || (eregi("Mac 10",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "_operatingSystem X";
				}
				elseif ((eregi("PowerPC",$this->_userAgent)) || (eregi("PPC",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "PPC";
				}
				elseif ((eregi("68000",$this->_userAgent)) || (eregi("68k",$this->_userAgent))) 
				{
					$this->_operatingSystemVersion = "68K";
				}
			}
			elseif (eregi("linux",$this->_userAgent))
			{
				$this->_operatingSystem = "Linux";
				if (eregi("i686",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i686";
				}
				elseif (eregi("i586",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i586";
				}
				elseif (eregi("i486",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i486";
				}
				elseif (eregi("i386",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i386";
				}
				elseif (eregi("ppc",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "ppc";
				}
			}
			elseif (eregi("sun_operatingSystem",$this->_userAgent))
			{
				$this->_operatingSystem = "Sun_operatingSystem";
			}
			elseif (eregi("hp-ux",$this->_userAgent))
			{
				$this->_operatingSystem = "HP-UX";
			}
			elseif (eregi("_operatingSystemf1",$this->_userAgent))
			{
				$this->_operatingSystem = "_operatingSystemF1";
			}
			elseif (eregi("freebsd",$this->_userAgent))
			{
				$this->_operatingSystem = "FreeBSD";
				if (eregi("i686",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i686";
				}
				elseif (eregi("i586",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i586";
				}
				elseif (eregi("i486",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i486";
				}
				elseif (eregi("i386",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i386";
				}
			}
			elseif (eregi("netbsd",$this->_userAgent))
			{
				$this->_operatingSystem = "NetBSD";
				if (eregi("i686",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i686";
				}
				elseif (eregi("i586",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i586";
				}
				elseif (eregi("i486",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i486";
				}
				elseif (eregi("i386",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "i386";
				}
			}
			elseif (eregi("irix",$this->_userAgent))
			{
				$this->_operatingSystem = "IRIX";
			}
			elseif (eregi("_operatingSystem/2",$this->_userAgent))
			{
				$this->_operatingSystem = "_operatingSystem/2";
				if (eregi("Warp 4.5",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "Warp 4.5";
				}
				elseif (eregi("Warp 4",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "Warp 4";
				}
			}
			elseif (eregi("amiga",$this->_userAgent))
			{
				$this->_operatingSystem = "Amiga";
			}
			elseif (eregi("liberate",$this->_userAgent))
			{
				$this->_operatingSystem = "Liberate";
			}
			elseif (eregi("qnx",$this->_userAgent))
			{
				$this->_operatingSystem = "QNX";
				if (eregi("photon",$this->_userAgent)) 
				{
					$this->_operatingSystemVersion = "Photon";
				}
			}
			elseif (eregi("dreamcast",$this->_userAgent))
			{
				$this->_operatingSystem = "Sega Dreamcast";
			}
			elseif (eregi("palm",$this->_userAgent))
			{
				$this->_operatingSystem = "Palm";
			}
			elseif (eregi("powertv",$this->_userAgent))
			{
				$this->_operatingSystem = "PowerTV";
			}
			elseif (eregi("prodigy",$this->_userAgent))
			{
				$this->_operatingSystem = "Prodigy";
			}
			elseif (eregi("symbian",$this->_userAgent))
			{
				$this->_operatingSystem = "Symbian";
				if (eregi("symbian_operatingSystem/6.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.1";
				}
			}
			elseif (eregi("unix",$this->_userAgent))
			{
				$this->_operatingSystem = "Unix";
			}
			elseif (eregi("webtv",$this->_userAgent))
			{
				$this->_operatingSystem = "WebTV";
			}
			elseif (eregi("sie-cx35",$this->_userAgent))
			{
				$this->_operatingSystem = "Siemens CX35";
			}
		}
		
		public function FindBrowser()
		{
			// boti
			if (eregi("msnbot",$this->_userAgent))
			{
				$this->_browser = "MSN Bot";
				$this->_type = "robot";
				if (eregi("msnbot/0.11",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.11";
				}
				elseif (eregi("msnbot/0.30",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.3";
				}
				elseif (eregi("msnbot/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("almaden",$this->_userAgent))
			{
				$this->_browser = "IBM Almaden Crawler";
				$this->_type = "robot";
			}
			elseif (eregi("BecomeBot",$this->_userAgent))
			{
				$this->_browser = "BecomeBot";
				if (eregi("becomebot/1.23",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.23";
				}
				$this->_type = "robot";
			}
			elseif (eregi("Link-Checker-Pro",$this->_userAgent))
			{
				$this->_browser = "Link Checker Pro";
				$this->_type = "robot";
			}
			elseif (eregi("ia_archiver",$this->_userAgent))
			{
				$this->_browser = "Alexa";
				$this->_type = "robot";
			}
			elseif ((eregi("googlebot",$this->_userAgent)) || (eregi("google",$this->_userAgent)))
			{
				$this->_browser = "Google Bot";
				$this->_type = "robot";
				if ((eregi("googlebot/2.1",$this->_userAgent)) || (eregi("google/2.1",$this->_userAgent))) 
				{
					$this->_browserVersion = "2.1";
				}
			}
			elseif (eregi("surveybot",$this->_userAgent))
			{
				$this->_browser = "Survey Bot";
				$this->_type = "robot";
				if (eregi("surveybot/2.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.3";
				}
			}
			elseif (eregi("zyborg",$this->_userAgent))
			{
				$this->_browser = "ZyBorg";
				$this->_type = "robot";
				if (eregi("zyborg/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("w3c-checklink",$this->_userAgent))
			{
				$this->_browser = "W3C Checklink";
				$this->_type = "robot";
				if (eregi("checklink/3.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.6";
				}
			}
			elseif (eregi("linkwalker",$this->_userAgent))
			{
				$this->_browser = "LinkWalker";
				$this->_type = "robot";
			}
			elseif (eregi("fast-webcrawler",$this->_userAgent))
			{
				$this->_browser = "Fast WebCrawler";
				$this->_type = "robot";
				if (eregi("webcrawler/3.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.8";
				}
			}
			elseif ((eregi("yahoo",$this->_userAgent)) && (eregi("slurp",$this->_userAgent)))
			{
				$this->_browser = "Yahoo! Slurp";
				$this->_type = "robot";
			}
			elseif (eregi("naverbot",$this->_userAgent))
			{
				$this->_browser = "NaverBot";
				$this->_type = "robot";
				if (eregi("dloader/1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
			}
			elseif (eregi("converacrawler",$this->_userAgent))
			{
				$this->_browser = "ConveraCrawler";
				$this->_type = "robot";
				if (eregi("converacrawler/0.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.5";
				}
			}
			elseif (eregi("w3c_validator",$this->_userAgent))
			{
				$this->_browser = "W3C Validator";
				$this->_type = "robot";
				if (eregi("w3c_validator/1.305",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.305";
				}
			}
			elseif (eregi("innerprisebot",$this->_userAgent))
			{
				$this->_browser = "Innerprise";
				$this->_type = "robot";
				if (eregi("innerprise/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("topicspy",$this->_userAgent))
			{
				$this->_browser = "Topicspy Checkbot";
				$this->_type = "robot";
			}
			elseif (eregi("poodle predictor",$this->_userAgent))
			{
				$this->_browser = "Poodle Predictor";
				$this->_type = "robot";
				if (eregi("poodle predictor 1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("ichiro",$this->_userAgent))
			{
				$this->_browser = "Ichiro";
				$this->_type = "robot";
				if (eregi("ichiro/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("link checker pro",$this->_userAgent))
			{
				$this->_browser = "Link Checker Pro";
				$this->_type = "robot";
				if (eregi("link checker pro 3.2.16",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.2.16";
				}
			}
			elseif (eregi("grub-client",$this->_userAgent))
			{
				$this->_browser = "Grub client";
				$this->_type = "robot";
				if (eregi("grub-client-2.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.3";
				}
			}
			elseif (eregi("gigabot",$this->_userAgent))
			{
				$this->_browser = "Gigabot";
				$this->_type = "robot";
				if (eregi("gigabot/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
			elseif (eregi("psbot",$this->_userAgent))
			{
				$this->_browser = "PSBot";
				$this->_type = "robot";
				if (eregi("psbot/0.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.1";
				}
			}
			elseif (eregi("mj12bot",$this->_userAgent))
			{
				$this->_browser = "MJ12Bot";
				$this->_type = "robot";
				if (eregi("mj12bot/v0.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.5";
				}
			}
			elseif (eregi("nextgensearchbot",$this->_userAgent))
			{
				$this->_browser = "NextGenSearchBot";
				$this->_type = "robot";
				if (eregi("nextgensearchbot 1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1";
				}
			}
			elseif (eregi("tutorgigbot",$this->_userAgent))
			{
				$this->_browser = "TutorGigBot";
				$this->_type = "robot";
				if (eregi("bot/1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
			}
			elseif (ereg("NG",$this->_userAgent))
			{
				$this->_browser = "Exabot NG";
				$this->_type = "robot";
				if (eregi("ng/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
			elseif (eregi("gaisbot",$this->_userAgent))
			{
				$this->_browser = "Gaisbot";
				$this->_type = "robot";
				if (eregi("gaisbot/3.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.0";
				}
			}
			elseif (eregi("xenu link sleuth",$this->_userAgent))
			{
				$this->_browser = "Xenu Link Sleuth";
				$this->_type = "robot";
				if (eregi("xenu link sleuth 1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
			}
			elseif (eregi("turnitinbot",$this->_userAgent))
			{
				$this->_browser = "TurnitinBot";
				$this->_type = "robot";
				if (eregi("turnitinbot/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
			elseif (eregi("iconsurf",$this->_userAgent))
			{
				$this->_browser = "IconSurf";
				$this->_type = "robot";
				if (eregi("iconsurf/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
			elseif (eregi("zoe indexer",$this->_userAgent))
			{
				$this->_browser = "Zoe Indexer";
				$this->_type = "robot";
				if (eregi("v1.x",$this->_userAgent)) 
				{
					$this->_browserVersion = "1";
				}
			}
			elseif (eregi("amaya",$this->_userAgent))
			{
				$this->_browser = "amaya";
				$this->_type = "_browser";
				if (eregi("amaya/5.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.0";
				}
				elseif (eregi("amaya/5.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.1";
				}
				elseif (eregi("amaya/5.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.2";
				}
				elseif (eregi("amaya/5.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.3";
				}
				elseif (eregi("amaya/6.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.0";
				}
				elseif (eregi("amaya/6.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.1";
				}
				elseif (eregi("amaya/6.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.2";
				}
				elseif (eregi("amaya/6.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.3";
				}
				elseif (eregi("amaya/6.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.4";
				}
				elseif (eregi("amaya/7.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.0";
				}
				elseif (eregi("amaya/7.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.1";
				}
				elseif (eregi("amaya/7.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.2";
				}
				elseif (eregi("amaya/8.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "8.0";
				}
			}
			elseif ((eregi("aol",$this->_userAgent)) && !(eregi("msie",$this->_userAgent)))
			{
				$this->_browser = "AOL";
				$this->_type = "_browser";
				if ((eregi("aol 7.0",$this->_userAgent)) || (eregi("aol/7.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.0";
				}
			}
			elseif ((eregi("aweb",$this->_userAgent)) || (eregi("amigavoyager",$this->_userAgent)))
			{
				$this->_browser = "AWeb";
				$this->_type = "_browser";
				if (eregi("voyager/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
				elseif (eregi("voyager/2.95",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.95";
				}
				elseif ((eregi("voyager/3",$this->_userAgent)) || (eregi("aweb/3.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "3.0";
				}
				elseif (eregi("aweb/3.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.1";
				}
				elseif (eregi("aweb/3.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.2";
				}
				elseif (eregi("aweb/3.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.3";
				}
				elseif (eregi("aweb/3.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.4";
				}
				elseif (eregi("aweb/3.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.9";
				}
			}
			elseif (eregi("beonex",$this->_userAgent))
			{
				$this->_browser = "Beonex";
				$this->_type = "_browser";
				if (eregi("beonex/0.8.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.8.2";
				}
				elseif (eregi("beonex/0.8.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.8.1";
				}
				elseif (eregi("beonex/0.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.8";
				}
			}
			elseif (eregi("camino",$this->_userAgent))
			{
				$this->_browser = "Camino";
				$this->_type = "_browser";
				if (eregi("camino/0.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.7";
				}
			}
			elseif (eregi("cyberdog",$this->_userAgent))
			{
				$this->_browser = "Cyberdog";
				$this->_type = "_browser";
				if (eregi("cybergog/1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
				elseif (eregi("cyberdog/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
				elseif (eregi("cyberdog/2.0b1",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0b1";
				}
			}
			elseif (eregi("dillo",$this->_userAgent))
			{
				$this->_browser = "Dillo";
				$this->_type = "_browser";
				if (eregi("dillo/0.6.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.6.6";
				}
				elseif (eregi("dillo/0.7.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.7.2";
				}
				elseif (eregi("dillo/0.7.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.7.3";
				}
				elseif (eregi("dillo/0.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.8";
				}
			}
			elseif (eregi("doris",$this->_userAgent))
			{
				$this->_browser = "Doris";
				$this->_type = "_browser";
				if (eregi("doris/1.10",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.10";
				}
			}
			elseif (eregi("emacs",$this->_userAgent))
			{
				$this->_browser = "Emacs";
				$this->_type = "_browser";
				if (eregi("emacs/w3/2",$this->_userAgent)) 
				{
					$this->_browserVersion = "2";
				}
				elseif (eregi("emacs/w3/3",$this->_userAgent)) 
				{
					$this->_browserVersion = "3";
				}
				elseif (eregi("emacs/w3/4",$this->_userAgent)) 
				{
					$this->_browserVersion = "4";
				}
			}
			elseif (eregi("firebird",$this->_userAgent))
			{
				$this->_browser = "Firebird";
				$this->_type = "_browser";
				if ((eregi("firebird/0.6",$this->_userAgent)) || (eregi("_browser/0.6",$this->_userAgent))) 
				{
					$this->_browserVersion = "0.6";
				}
				elseif (eregi("firebird/0.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.7";
				}
			}
			elseif (eregi("firefox",$this->_userAgent))
			{
				$this->_browser = "Firefox";
				$this->_type = "_browser";
				if (eregi("firefox/0.9.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.9.1";
				}
				elseif (eregi("firefox/0.10",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.10";
				}
				elseif (eregi("firefox/0.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.9";
				}
				elseif (eregi("firefox/0.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.8";
				}
				elseif (eregi("firefox/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
				elseif (eregi("firefox/1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
				elseif (eregi("firefox/1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
				elseif (eregi("firefox/1.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.3";
				}
				elseif (eregi("firefox/1.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.4";
				}
				elseif (eregi("firefox/1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}

			}
			elseif (eregi("frontpage",$this->_userAgent))
			{
				$this->_browser = "FrontPage";
				$this->_type = "_browser";
				if ((eregi("express 2",$this->_userAgent)) || (eregi("frontpage 2",$this->_userAgent))) 
				{
					$this->_browserVersion = "2";
				}
				elseif (eregi("frontpage 3",$this->_userAgent)) 
				{
					$this->_browserVersion = "3";
				}
				elseif (eregi("frontpage 4",$this->_userAgent)) 
				{
					$this->_browserVersion = "4";
				}
				elseif (eregi("frontpage 5",$this->_userAgent)) 
				{
					$this->_browserVersion = "5";
				}
				elseif (eregi("frontpage 6",$this->_userAgent)) 
				{
					$this->_browserVersion = "6";
				}
			}
			elseif (eregi("galeon",$this->_userAgent))
			{
				$this->_browser = "Galeon";
				$this->_type = "_browser";
				if (eregi("galeon 0.1",$this->_userAgent)) {$this->_browserVersion = "0.1";}
				elseif (eregi("galeon/0.11.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.11.1";
				}
				elseif (eregi("galeon/0.11.2",$this->_userAgent))
				{
					$this->_browserVersion = "0.11.2";
				}
				elseif (eregi("galeon/0.11.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.11.3";
				}
				elseif (eregi("galeon/0.11.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.11.5";
				}
				elseif (eregi("galeon/0.12.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.8";
				}
				elseif (eregi("galeon/0.12.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.7";
				}
				elseif (eregi("galeon/0.12.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.6";
				}
				elseif (eregi("galeon/0.12.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.5";
				}
				elseif (eregi("galeon/0.12.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.4";
				}
				elseif (eregi("galeon/0.12.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.3";
				}
				elseif (eregi("galeon/0.12.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.2";
				}
				elseif (eregi("galeon/0.12.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12.1";
				}
				elseif (eregi("galeon/0.12",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.12";
				}
				elseif ((eregi("galeon/1",$this->_userAgent)) || (eregi("galeon 1.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif (eregi("ibm web _browser",$this->_userAgent))
			{
				$this->_browser = "IBM Web _browser";
				$this->_type = "_browser";
				if (eregi("rv:1.0.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0.1";
				}
			}
			elseif (eregi("chimera",$this->_userAgent))
			{
				$this->_browser = "Chimera";
				$this->_type = "_browser";
				if (eregi("chimera/0.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.7";
				}
				elseif (eregi("chimera/0.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.6";
				}
				elseif (eregi("chimera/0.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.5";
				}
				elseif (eregi("chimera/0.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.4";
				}
			}
			elseif (eregi("icab",$this->_userAgent))
			{
				$this->_browser = "iCab";
        		$this->_type = "_browser";
				if (eregi("icab/2.7.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.7.1";
				}
				elseif (eregi("icab/2.8.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.8.1";
				}
				elseif (eregi("icab/2.8.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.8.2";
				}
				elseif (eregi("icab 2.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.9";
				}
				elseif (eregi("icab 2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
			elseif (eregi("konqueror",$this->_userAgent))
			{
				$this->_browser = "Konqueror";
				$this->_type = "_browser";
				if (eregi("konqueror/3.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.1";
				}
				elseif (eregi("konqueror/3.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.3";
				}
				elseif (eregi("konqueror/3.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.2";
				}
				elseif (eregi("konqueror/3",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.0";
				}
				elseif (eregi("konqueror/2.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.2";
				}
				elseif (eregi("konqueror/2.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.1";
				}
				elseif (eregi("konqueror/1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
			}
			elseif (eregi("liberate",$this->_userAgent))
			{
				$this->_browser = "Liberate";
				$this->_type = "_browser";
				if (eregi("dtv 1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
				elseif (eregi("dtv 1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
			}
			elseif (eregi("desktop/lx",$this->_userAgent))
			{
				$this->_browser = "Lycoris Desktop/LX";
				$this->_type = "_browser";
			}
			elseif (eregi("netbox",$this->_userAgent))
			{
				$this->_browser = "NetBox";
				$this->_type = "_browser";
				if (eregi("netbox/3.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.5";
				}
			}
			elseif (eregi("netcaptor",$this->_userAgent))
			{
				$this->_browser = "Netcaptor";
				$this->_type = "_browser";
				if (eregi("netcaptor 7.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.0";
				}
				elseif (eregi("netcaptor 7.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.1";
				}
				elseif (eregi("netcaptor 7.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.2";
				}
				elseif (eregi("netcaptor 7.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.5";
				}
				elseif (eregi("netcaptor 6.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.1";
				}
			}
			elseif (eregi("netpliance",$this->_userAgent))
			{
				$this->_browser = "Netpliance";
				$this->_type = "_browser";
			}
			elseif (eregi("netscape",$this->_userAgent)) 
			{
				$this->_browser = "Netscape";
				$this->_type = "_browser";
				if (eregi("netscape/7.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.1";
				}
				elseif (eregi("netscape/7.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.2";
				}
				elseif (eregi("netscape/7.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "7.0";
				}
				elseif (eregi("netscape6/6.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.2";
				}
				elseif (eregi("netscape6/6.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.1";
				}
				elseif (eregi("netscape6/6.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.0";
				}
			}
			elseif ((eregi("mozilla/5.0",$this->_userAgent)) && (eregi("rv:",$this->_userAgent)) && (eregi("gecko/",$this->_userAgent)))
			{
				$this->_browser = "Mozilla";
				$this->_type = "_browser";
				if (eregi("rv:1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
				elseif (eregi("rv:1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
				elseif (eregi("rv:1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
				elseif (eregi("rv:1.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.3";
				}
				elseif (eregi("rv:1.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.4";
				}
				elseif (eregi("rv:1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
				elseif (eregi("rv:1.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.6";
				}
				elseif (eregi("rv:1.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.7";
				}
				elseif (eregi("rv:1.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.8";
				}
			}
			elseif (eregi("offbyone",$this->_userAgent))
			{
				$this->_browser = "OffByOne";
				$this->_type = "_browser";
				if (eregi("mozilla/4.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.4";
				}
			}
			elseif (eregi("omniweb",$this->_userAgent))
			{
				$this->_browser = "OmniWeb";
				$this->_type = "_browser";
				if (eregi("omniweb/4.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.5";
				}
				elseif (eregi("omniweb/4.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.4";
				}
				elseif (eregi("omniweb/4.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.3";
				}
				elseif (eregi("omniweb/4.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.2";
				}
				elseif (eregi("omniweb/4.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.1";
				}
			}
			elseif (eregi("opera",$this->_userAgent))
			{
				$this->_browser = "Opera";
				$this->_type = "_browser";
				if ((eregi("opera/7.21",$this->_userAgent)) || (eregi("opera 7.21",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.21";
				}
				elseif ((eregi("opera/8.0",$this->_userAgent)) || (eregi("opera 8.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "8.0";
				}
				elseif ((eregi("opera/7.60",$this->_userAgent)) || (eregi("opera 7.60",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.60";
				}
				elseif ((eregi("opera/7.54",$this->_userAgent)) || (eregi("opera 7.54",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.54";
				}
				elseif ((eregi("opera/7.53",$this->_userAgent)) || (eregi("opera 7.53",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.53";
				}
				elseif ((eregi("opera/7.52",$this->_userAgent)) || (eregi("opera 7.52",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.52";
				}
				elseif ((eregi("opera/7.51",$this->_userAgent)) || (eregi("opera 7.51",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.51";
				}
				elseif ((eregi("opera/7.50",$this->_userAgent)) || (eregi("opera 7.50",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.50";
				}
				elseif ((eregi("opera/7.23",$this->_userAgent)) || (eregi("opera 7.23",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.23";
				}
				elseif ((eregi("opera/7.22",$this->_userAgent)) || (eregi("opera 7.22",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.22";
				}
				elseif ((eregi("opera/7.20",$this->_userAgent)) || (eregi("opera 7.20",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.20";
				}
				elseif ((eregi("opera/7.11",$this->_userAgent)) || (eregi("opera 7.11",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.11";
				}
				elseif ((eregi("opera/7.10",$this->_userAgent)) || (eregi("opera 7.10",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.10";
				}
				elseif ((eregi("opera/7.03",$this->_userAgent)) || (eregi("opera 7.03",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.03";
				}
				elseif ((eregi("opera/7.02",$this->_userAgent)) || (eregi("opera 7.02",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.02";
				}
				elseif ((eregi("opera/7.01",$this->_userAgent)) || (eregi("opera 7.01",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.01";
				}
				elseif ((eregi("opera/7.0",$this->_userAgent)) || (eregi("opera 7.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "7.0";
				}
				elseif ((eregi("opera/6.12",$this->_userAgent)) || (eregi("opera 6.12",$this->_userAgent))) 
				{
					$this->_browserVersion = "6.12";
				}
				elseif ((eregi("opera/6.11",$this->_userAgent)) || (eregi("opera 6.11",$this->_userAgent))) 
				{
					$this->_browserVersion = "6.11";
				}
				elseif ((eregi("opera/6.1",$this->_userAgent)) || (eregi("opera 6.1",$this->_userAgent))) 
				{
					$this->_browserVersion = "6.1";
				}
				elseif ((eregi("opera/6.	0",$this->_userAgent)) || (eregi("opera 6.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "6.0";
				}
				elseif ((eregi("opera/5.12",$this->_userAgent)) || (eregi("opera 5.12",$this->_userAgent))) 
				{
					$this->_browserVersion = "5.12";
				}
				elseif ((eregi("opera/5.0",$this->_userAgent)) || (eregi("opera 5.0",$this->_userAgent))) 
				{
					$this->_browserVersion = "5.0";
				}
				elseif ((eregi("opera/4",$this->_userAgent)) || (eregi("opera 4",$this->_userAgent))) 
				{
					$this->_browserVersion = "4";
				}
			}
			elseif (eregi("oracle",$this->_userAgent))
			{
				$this->_browser = "Oracle Power_browser";
				$this->_type = "_browser";
				if (eregi("(tm)/1.0a",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0a";
				}
				elseif (eregi("oracle 1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
			}
			elseif (eregi("phoenix",$this->_userAgent))
			{
				$this->_browser = "Phoenix";
				$this->_type = "_browser";
				if (eregi("phoenix/0.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.4";
				}
				elseif (eregi("phoenix/0.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.5";
				}
			}
			elseif (eregi("planetweb",$this->_userAgent))
			{
				$this->_browser = "PlanetWeb";
				$this->_type = "_browser";
				if (eregi("planetweb/2.606",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.6";
				}
				elseif (eregi("planetweb/1.125",$this->_userAgent)) 
				{
					$this->_browserVersion = "3";
				}
			}
			elseif (eregi("powertv",$this->_userAgent))
			{
				$this->_browser = "PowerTV";
				$this->_type = "_browser";
				if (eregi("powertv/1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
			}
			elseif (eregi("prodigy",$this->_userAgent))
			{
				$this->_browser = "Prodigy";
				$this->_type = "_browser";
				if (eregi("wb/3.2e",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.2e";
				}
				elseif (eregi("rv: 1.",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
			}
			elseif ((eregi("voyager",$this->_userAgent)) || ((eregi("qnx",$this->_userAgent))) && (eregi("rv: 1.",$this->_userAgent)))
			{
				$this->_browser = "Voyager";
        $this->_type = "_browser";
				if (eregi("2.03b",$this->_userAgent))
				{
					$this->_browserVersion = "2.03b";
				}
				elseif (eregi("wb/win32/3.4g",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.4g";
				}
			}
			elseif (eregi("quicktime",$this->_userAgent))
			{
				$this->_browser = "QuickTime";
				$this->_type = "_browser";
				if (eregi("qtver=5",$this->_userAgent)) {$this->_browserVersion = "5.0";}
				elseif (eregi("qtver=6.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.0";
				}
				elseif (eregi("qtver=6.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.1";
				}
				elseif (eregi("qtver=6.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.2";
				}
				elseif (eregi("qtver=6.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.3";
				}
				elseif (eregi("qtver=6.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.4";
				}
				elseif (eregi("qtver=6.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.5";
				}
			}
			elseif (eregi("safari",$this->_userAgent))
			{
				$this->_browser = "Safari";
				$this->_type = "_browser";
				if (eregi("safari/48",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.48";
				}
				elseif (eregi("safari/49",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.49";
				}
				elseif (eregi("safari/51",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.51";
				}
				elseif (eregi("safari/60",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.60";
				}
				elseif (eregi("safari/61",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.61";
				}
				elseif (eregi("safari/62",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.62";
				}
				elseif (eregi("safari/63",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.63";
				}
				elseif (eregi("safari/64",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.64";
				}
				elseif (eregi("safari/65",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.65";
				}
				elseif (eregi("safari/66",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.66";
				}
				elseif (eregi("safari/67",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.67";
				}
				elseif (eregi("safari/68",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.68";
				}
				elseif (eregi("safari/69",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.69";
				}
				elseif (eregi("safari/70",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.70";
				}
				elseif (eregi("safari/71",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.71";
				}
				elseif (eregi("safari/72",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.72";
				}
				elseif (eregi("safari/73",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.73";
				}
				elseif (eregi("safari/74",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.74";
				}
				elseif (eregi("safari/80",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.80";
				}
				elseif (eregi("safari/83",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.83";
				}
				elseif (eregi("safari/84",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.84";
				}
				elseif (eregi("safari/85",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.85";
				}
				elseif (eregi("safari/90",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.90";
				}
				elseif (eregi("safari/92",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.92";
				}
				elseif (eregi("safari/93",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.93";
				}
				elseif (eregi("safari/94",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.94";
				}
				elseif (eregi("safari/95",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.95";
				}
				elseif (eregi("safari/96",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.96";
				}
				elseif (eregi("safari/97",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.97";
				}
				elseif (eregi("safari/125",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.25";
				}
			}
			elseif (eregi("sextatnt",$this->_userAgent))
			{
				$this->_browser = "Tango";
				$this->_type = "_browser";
				if (eregi("sextant v3.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.0";
				}
			}
			elseif (eregi("sharpreader",$this->_userAgent))
			{
				$this->_browser = "SharpReader";
				$this->_type = "_browser";
				if (eregi("sharpreader/0.9.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.9.5";
				}
			}
			elseif (eregi("elinks",$this->_userAgent))
			{
				$this->_browser = "ELinks";
				$this->_type = "_browser";
				if (eregi("0.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.3";
				}
				elseif (eregi("0.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.4";
				}
				elseif (eregi("0.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.9";
				}
			}
			elseif (eregi("links",$this->_userAgent))
			{
				$this->_browser = "Links";
				$this->_type = "_browser";
				if (eregi("0.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "0.9";
				}
				elseif (eregi("2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
				elseif (eregi("2.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.1";
				}
			}
			elseif (eregi("lynx",$this->_userAgent))
			{
				$this->_browser = "Lynx";
				$this->_type = "_browser";
				if (eregi("lynx/2.3",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.3";
				}
				elseif (eregi("lynx/2.4",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.4";
				}
				elseif ((eregi("lynx/2.5",$this->_userAgent)) || (eregi("lynx 2.5",$this->_userAgent))) 
				{
					$this->_browserVersion = "2.5";
				}
				elseif (eregi("lynx/2.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.6";
				}
				elseif (eregi("lynx/2.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.7";
				}
				elseif (eregi("lynx/2.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.8";
				}
			}
			elseif (eregi("webexplorer",$this->_userAgent))
			{
				$this->_browser = "WebExplorer";
				$this->_type = "_browser";
				if (eregi("dll/v1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
			}
			elseif (eregi("wget",$this->_userAgent))
			{
				$this->_browser = "WGet";
				$this->_type = "_browser";
				if (eregi("Wget/1.9",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.9";
				}
				if (eregi("Wget/1.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.8";
				}
			}
			elseif (eregi("webtv",$this->_userAgent))
			{
				$this->_browser = "WebTV";
				$this->_type = "_browser";
				if (eregi("webtv/1.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.0";
				}
				elseif (eregi("webtv/1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
				elseif (eregi("webtv/1.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.2";
				}
				elseif (eregi("webtv/2.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.2";
				}
				elseif (eregi("webtv/2.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.5";
				}
				elseif (eregi("webtv/2.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.6";
				}
				elseif (eregi("webtv/2.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.7";
				}
			}
			elseif (eregi("yandex",$this->_userAgent))
			{
				$this->_browser = "Yandex";
				$this->_type = "_browser";
				if (eregi("/1.01",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.01";
				}
				elseif (eregi("/1.03",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.03";
				}
			}
			elseif ((eregi("mspie",$this->_userAgent)) || ((eregi("msie",$this->_userAgent))) && (eregi("windows ce",$this->_userAgent)))
			{
				$this->_browser = "Pocket Internet Explorer";
				$this->_type = "_browser";
				if (eregi("mspie 1.1",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.1";
				}
				elseif (eregi("mspie 2.0",$this->_userAgent))
				{
					$this->_browserVersion = "2.0";
				}
				elseif (eregi("msie 3.02",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.02";
				}
			}
			elseif (eregi("UP._browser/",$this->_userAgent))
			{
				$this->_browser = "UP _browser";
				$this->_type = "_browser";
				if (eregi("_browser/7.0",$this->_userAgent))
				{
					$this->_browserVersion = "7.0";
				}
			}
			elseif (eregi("msie",$this->_userAgent))
			{
				$this->_browser = "Internet Explorer";
				$this->_type = "_browser";
				if (eregi("msie 6.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "6.0";
				}
				elseif (eregi("msie 5.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.5";
				}
				elseif (eregi("msie 5.01",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.01";
				}
				elseif (eregi("msie 5.23",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.23";
				}
				elseif (eregi("msie 5.22",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.22";
				}
				elseif (eregi("msie 5.2.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.2.2";
				}
				elseif (eregi("msie 5.1b1",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.1b1";
				}
				elseif (eregi("msie 5.17",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.17";
				}
				elseif (eregi("msie 5.16",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.16";
				}
				elseif (eregi("msie 5.12",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.12";
				}
				elseif (eregi("msie 5.0b1",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.0b1";
				}
				elseif (eregi("msie 5.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.0";
				}
				elseif (eregi("msie 5.21",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.21";
				}
				elseif (eregi("msie 5.2",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.2";
				}
				elseif (eregi("msie 5.15",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.15";
				}
				elseif (eregi("msie 5.14",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.14";
				}
				elseif (eregi("msie 5.13",$this->_userAgent)) 
				{
					$this->_browserVersion = "5.13";
				}
				elseif (eregi("msie 4.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.5";
				}
				elseif (eregi("msie 4.01",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.01";
				}
				elseif (eregi("msie 4.0b2",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.0b2";
				}
				elseif (eregi("msie 4.0b1",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.0b1";
				}
				elseif (eregi("msie 4",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.0";
				}
				elseif (eregi("msie 3",$this->_userAgent))
				{
					$this->_browserVersion = "3.0";
				}
				elseif (eregi("msie 2",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
				elseif (eregi("msie 1.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "1.5";
				}
			}
			elseif (eregi("iexplore",$this->_userAgent))
			{
				$this->_browser = "Internet Explorer";
				$this->_type = "_browser";
			}
			elseif (eregi("mozilla",$this->_userAgent)) 
			{
				$this->_browser = "Netscape";
				$this->_type = "_browser";
				if (eregi("mozilla/4.8",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.8";
				}
				elseif (eregi("mozilla/4.7",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.7";
				}
				elseif (eregi("mozilla/4.6",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.6";
				}
				elseif (eregi("mozilla/4.5",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.5";
				}
				elseif (eregi("mozilla/4.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "4.0";
				}
				elseif (eregi("mozilla/3.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "3.0";
				}
				elseif (eregi("mozilla/2.0",$this->_userAgent)) 
				{
					$this->_browserVersion = "2.0";
				}
			}
		}
		
	}
?>