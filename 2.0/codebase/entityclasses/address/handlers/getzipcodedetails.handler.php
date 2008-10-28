<?php
/**
 * Zip Code Details Handler Class
 * 
 * @package Sandstone
 * @subpackage Address
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * @version 1.0
 * 
 * @copyright 2006 Designing Interactive
 * 
 * @todo
 * 
 */

NameSpace::Using("Sandstone.ADOdb");
Namespace::Using("Sandstone.Application.Dispatcher");

class GetZipCodeDetails_Handler extends EventHandler 
{
	public function HandleEvent()
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	ZipCode, 
							City, 
							StateCode,
							Latitude,
							Longitude,
							GMToffset,
							IsDaylightSavings
				 	FROM 	core_ZipCodeMaster 
				 	WHERE 	ZipCode LIKE '{$this->_dispatcher->Data['ZipCode']}%'
				 	LIMIT 5";
		
		$ds = $conn->Execute($query);
		
		echo "<ul>";
		
		while ($dr = $ds->FetchRow())
		{
			$ZipCode = new ZipCode($dr);
			echo "<li><span class=\"informal\">" . $ZipCode->City . ", " . $ZipCode->State->StateCode	. " </span>" . $ZipCode->ZipCode . "</li>";
		}
		
		echo "</ul>";
	}
}

?>