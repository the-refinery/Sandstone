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

document.observe('dom:loaded',function()
{
	// Find all titletextboxes by looking for class name
	$$(".titletextbox_body").each(function(element) 
	{
		if ($F(element) == '')
		{
			element.value = $(element.id + '_Label').innerHTML;
			element.addClassName('titletextbox_blank');
		}
		$(element.id + '_Label').hide();
		
		element.observe("focus",function(event)
		{
			var target = event.element();

			if ($F(target) == $(target.id + '_Label').innerHTML)
			{
				target.removeClassName('titletextbox_blank');
				target.value = '';
			}

			target.fire("controls:titletextbox:focus:callback");
	  	});
	
		element.observe("blur", function(event)
		{
			var target = event.element();

			if ($F(target) == '')
			{
				target.addClassName('titletextbox_blank');
				target.value = $(target.id + '_Label').innerHTML;
			}

			target.fire("controls:titletextbox:blur:callback");
		});
	});
});


