function RaiseAJAXevent(Target, Method, PostParameters, GetParameters)
{
	// PostParameters = Hash
	// Get Parameters = Query String
	PostParameters = $.extend({'target':Target, 'method':Method}, PostParameters);
	
	if (GetParameters == null)
	{
		GetParameters = '';
	}
	
	$.post(AJAX_URL+'?'+GetParameters, PostParameters, null, "script");	
}

