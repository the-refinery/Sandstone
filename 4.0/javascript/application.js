function FullFormAJAX(Target, Method, FormID)
{
	var FormParameters = Form.serialize(FormID);
	RaiseAJAXevent(Target, Method, FormParameters);
}

function RaiseAJAXevent(Target, Method, PostParameters)
{
	PostParameters = PostParameters + "&target=" + Target + "&method=" + Method;
	new Ajax.Request(AJAX_URL, 
		{
			postBody:PostParameters, 
			onFailure:function(t) { alert('An AJAX error has happened!'); }
		});
}