function RaiseAJAXevent(Target, Method, PostParameters)
{
	PostParameters = $.extend({'target':Target, 'method':Method}, PostParameters);
	
	$.post(AJAX_URL, PostParameters, null, "script");	
}

