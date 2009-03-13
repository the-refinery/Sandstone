function RaiseAJAXevent(Target, Method, PostParameters, GetParameters)
{
	// PostParameters = Hash
	// Get Parameters = Query String
	PostParameters = $.extend({'target':Target, 'method':Method}, PostParameters);
		
	$.post(AJAX_URL+'?'+GetParameters, PostParameters, null, "script");	
}

