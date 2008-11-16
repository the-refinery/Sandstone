function SetControlMessage(ControlName, MessageText)
{
	var messageDOM = $(ControlName + '_Message');
	var existingMessageLength = messageDOM.innerHTML.length;

	if (MessageText.length == 0 && existingMessageLength > 0)
	{
		// We had a message, but not it has been cleared
		new Effect.BlindUp(messageDOM,
			{
				afterFinish:function() { messageDOM.innerHTML = MessageText; }
			});
	}
	else if (MessageText.length > 0)
	{
		messageDOM.update(MessageText);

		if (existingMessageLength == 0)
		{
			new Effect.BlindDown(messageDOM);
		}
		else
		{
			new Effect.Shake(messageDOM);
		}
	}
}

function SelectDropdownItem(DomID, Value)
{
    $(DomID)[0].selected = true;

	var i;
    for (i = 0; i < $(DomID).length; i++)
    {
        if ( $(DomID)[i].value == Value )
        {
            $(DomID)[i].selected = true;
        }
    }
}

