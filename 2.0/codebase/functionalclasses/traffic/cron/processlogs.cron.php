<?php
/**
 * Traffic Process Cron Job
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

$IS_CRON = true;

// REQUIRE FRAMEWORK
require("/home/barracud/BarracudaSuite/Alpha/framework/framework.inc.php");

$licenseDBhost = "barracudasuite.com";
$licenseDBname = "barracud_master";
$licenseDBuser = "barracud_license";
$licenseDBpass = "barracudasuite";

//Get license records for all customers that have 
//the newsletter module
$conn = NewADOConnection('mysql');

$conn->Connect($licenseDBhost, $licenseDBuser, $licenseDBpass, $licenseDBname);

$query = "	SELECT 	SiteID
			FROM	admin_SiteMaster";

$ds = $conn->Execute($query);

if ($ds && $ds->RecordCount() > 0)
{
	while ($dr = $ds->FetchRow()) 
	{
	   //Setup the License for this site.
		$LICENSE = new License($dr['SiteID']);
		
		if ($LICENSE->IsValid)
		{	
			//Valid License, ok to process.
		
			// CONFIGURATION
			$DB_CONFIG = $LICENSE->DBconfigArray;
		
			$CONFIG = new Registry;
			
			/**
			 * The cron's tasks are determined by what date it currently is.  
			 * The first of the year and first of the month run additional 
			 * tasks that the daily cron doesn't do.
			 */
			
			$traffic = new Traffic(null, true);
			
			$currentdate = new Date(date("m/d/Y"));
			
			// CHECK FOR 1st DAY OF YEAR
			if ($currentdate->FormatDate("m") == "01" && $currentdate->FormatDate("d") == "01")
			{
				$traffic->CopyDailyToFullHistory();
				$traffic->CreateDailySummary();
				$traffic->CreateMonthlySummary();
				$traffic->CreateAnnualSummary();
				$traffic->CleanupDailyLog();
				$traffic->CleanupFullHistory();
				$traffic->CleanupDailySummary();
				$traffic->CleanupMonthlySummary();
				$traffic->CleanupAnnualSummary();
				$traffic->ResetMonthToDate();
				$traffic->ResetYearToDate();
			}
			// CHECK FOR 1st DAY OF MONTH, BUT NOT JANUARY
			elseif ($currentdate->FormatDate("d") == "01") // Doesn't run for january because that would have been true for 1st of year
			{
				$traffic->CopyDailyToFullHistory();
				$traffic->CreateDailySummary();
				$traffic->CreateMonthlySummary();
				$traffic->UpdateYearToDate();
				$traffic->CleanupDailyLog();
				$traffic->CleanupFullHistory();
				$traffic->CleanupDailySummary();
				$traffic->CleanupMonthlySummary();
				$traffic->ResetMonthToDate();
			}
			// TODAY IS NOT THE FIRST OF A MONTH OR YEAR
			else 
			{
				$traffic->CopyDailyToFullHistory();
				$traffic->CreateDailySummary();
				$traffic->UpdateMonthToDate();
				$traffic->UpdateYearToDate();
				$traffic->CleanupDailyLog();
				$traffic->CleanupFullHistory();
				$traffic->CleanupDailySummary();
			}
		}
	}
}
?>