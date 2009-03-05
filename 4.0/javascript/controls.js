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
// 
// function SelectDropdownItem(DomID, Value)
// {
//     $(DomID)[0].selected = true;
// 
// 	var i;
//     for (i = 0; i < $(DomID).length; i++)
//     {
//         if ( $(DomID)[i].value == Value )
//         {
//             $(DomID)[i].selected = true;
//         }
//     }
// }

// === Title Textbox ===

// document.observe('dom:loaded',function()
// {
// 	// Find all titletextboxes by looking for class name
// 	$$(".titletextbox_body").each(function(element) 
// 	{
// 		if ($F(element) == '')
// 		{
// 			element.value = $(element.id + '_Label').innerHTML;
// 			element.addClassName('titletextbox_blank');
// 		}
// 		$(element.id + '_Label').hide();
// 		
// 		element.observe("focus",function(event)
// 		{
// 			var target = event.element();
// 
// 			if ($F(target) == $(target.id + '_Label').innerHTML)
// 			{
// 				target.removeClassName('titletextbox_blank');
// 				target.value = '';
// 			}
// 
// 			target.fire("controls:titletextbox:focus:callback");
// 	  	});
// 	
// 		element.observe("blur", function(event)
// 		{
// 			var target = event.element();
// 
// 			if ($F(target) == '')
// 			{
// 				target.addClassName('titletextbox_blank');
// 				target.value = $(target.id + '_Label').innerHTML;
// 			}
// 
// 			target.fire("controls:titletextbox:blur:callback");
// 		});
// 	});
// });
// 
// 
