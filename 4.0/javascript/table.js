function AlternateTableRowStyles(Selector, oddClass, evenClass)
{	
	if (!oddClass) oddClass = "odd";
	if (!evenClass) evenClass = "even";
	
	$$('table.'+Selector+' tbody > tr:nth-child(odd)').each(function(s) 
	{
		s.addClassName(oddClass);
	});
	
    $$('table#'+Selector+' tbody > tr:nth-child(even)').each(function(s) 
	{
        s.addClassName(evenClass);
    });
}

