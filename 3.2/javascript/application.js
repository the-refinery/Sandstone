function getElementsByClassName(ClassName, TagName, ParentElement)
{
	// Set Argument Default Values if Necessary
	var testClass = new RegExp("(^|\\s)" + ClassName + "(\\s|$)");
	var TagName = TagName || "*";
	var ParentElement = ParentElement || document;
	
	// Get Elements Matching Tag Name under the Parent
	var elements = (TagName == "*" && ParentElement.all)? ParentElement.all : ParentElement.getElementsByTagName(TagName);
		
	var returnValue = [];
	var current;
	var length = elements.length;
	var i;
	
	for(i = 0; i < length; i++)
	{
		current = elements[i];
		
		if(testClass.test(current.className))
		{
			returnValue.push(current);
		}
	}
	
	return returnValue;
}