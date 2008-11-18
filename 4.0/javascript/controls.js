function SetControlMessage(ControlName, MessageText)
{
	var	messageDOM = $(ControlName + '_Message'),
		existingMessageLength = messageDOM.innerHTML.length;

	if (MessageText.length == 0 && existingMessageLength > 0)
	{
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

// === Title Textbox ===

document.observe("controls:titletextbox:focus", function(event)
{
	var target = event.element();
	
	if (target.value == event.memo.labelText)
	{
		target.removeClassName('titletextbox_blank');
		target.value = '';
	}
	
	target.fire("controls:titletextbox:focus:callback");
});

document.observe("controls:titletextbox:blur", function(event)
{
	var target = event.element();
	
	if (target.value == '')
	{
		target.addClassName('titletextbox_blank');
		target.value = event.memo.labelText;
	}
	
	target.fire("controls:titletextbox:blur:callback");
});

document.observe("controls:titletextbox:load", function(event)
{
	var target = event.element();
	
	if ($F(target) == '')
	{
		target.value = event.memo.labelText;
		target.addClassName('titletextbox_blank');
	}

	$(target.id + '_Label').hide();
	
	target.fire("controls:titletextbox:load:callback");
});