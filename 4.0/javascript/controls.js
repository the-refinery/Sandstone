function SetControlMessage(ControlName, MessageText)
{
	var	messageDOMselector = '#' + ControlName + '_Message';
	var existingMessageLength;
	
	if ($(messageDOMselector).length)
	{
		existingMessageLength = $(messageDOMselector).html().length;
	}
	else
	{
		existingMessageLength = 0;
	}
	
	if (MessageText.length == 0 && existingMessageLength > 0)
	{
		$(messageDOMselector).hide('blind',
			{	complete: function() 
				{ 
					$(messageDOMselector).remove(); 
				}
			});		
	}
	else if (MessageText.length > 0)
	{
		if ($(messageDOMselector).length == 0)
		{
			$('#' + ControlName).before('<div id="'+ControlName+'_Message" class="control_message"></div>');
			$(messageDOMselector).hide();
			$(messageDOMselector).show('blind');
		} 
  	
		$(messageDOMselector).html(MessageText);

		if (existingMessageLength == 0)
		{
			$(messageDOMselector);
		}
		else
		{
			$(messageDOMselector).effect('shake', { times: 3 });
		}
	}
}

function SelectDropdownItem(DomID, Value)
{
    $("#"+ DomID + " option[value='" + Value + "']").attr('selected', 'selected');
}

$(document).ready(function()
{
	// === Title Textbox ===
	$("input.titletextbox_body").each(function() 
	{
		if ($(this).val() == '')
		{
			$(this).val($('#' + $(this).attr('id') + '_Label').html());
			$(this).addClass('titletextbox_blank');
		}
		$('#' + $(this).attr('id') + '_Label').hide();
		
		$(this).bind("focus",function(event)
		{
			if ($(this).val() == $('#' + $(this).attr('id') + '_Label').html())
			{
				$(this).removeClass('titletextbox_blank');
				$(this).val('');
			}
	  	});
	
		$(this).bind("blur", function(event)
		{
			if ($(this).val() == '')
			{
				$(this).addClass('titletextbox_blank');
				$(this).val($('#' + $(this).attr('id') + '_Label').html());
			}
		});
	});
	
	// === Autocomplete ===
	$("input.autocomplete_textbox").each(function() 
	{	
		$('#' + $(this).attr('id')).autocomplete(AJAX_URL,{
			minChars: 3,
			extraParams:{target: $(this).attr('id').replace('_query',''), method: 'autocomplete'}
		});
		
		$('#' + $(this).attr('id')).result(function(event, data, formatted) {
			if (data)
			{
				$(this).val(data[0]);
				$('#' + $(this).attr('id').replace('_query','')).val(data[1]);
			}
		});
		
	});
});


