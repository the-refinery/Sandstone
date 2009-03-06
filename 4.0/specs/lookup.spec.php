<?php

class LookupSpec extends SpecBase
{
	public function ItShouldGiveMeAListOfUsers()
	{
		$users = LookupObjectSet('User','All');
		
		Check($users->ItemsByKey)->ShouldBeOfType('array');
	}

	public function ItShouldGiveMeNullForNoData()
	{
		$users = LookupObjectSet('User','ByRole',array('roleid'=>1));
		
		Check($users->ItemsByKey)->ShouldBeNull();
	}
}
?>