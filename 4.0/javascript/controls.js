function SetControlMessage(ControlName, MessageText)
{
// 	var	messageDOM = ControlName + '_Message';
// 	var existingMessageLength;
// 		
// 	if ($(messageDOM))
// 	{
// 		existingMessageLength = $(messageDOM).innerHTML.length;
// 	}
// 	else
// 	{
// 		existingMessageLength = 0;
// 	}
// 
// 	if (MessageText.length == 0 && existingMessageLength > 0)
// 	{
// 		new Effect.BlindUp(messageDOM,
// 			{
// 				afterFinish:function() { $(messageDOM).remove(); }
// 			});
// 	}
// 	else if (MessageText.length > 0)
// 	{
// 		if (! $(messageDOM)) new Insertion.Before(ControlName,'<div id="'+ControlName+'_Message" class="control_message" style="display:none;"></div>');
//   	
// 		$(messageDOM).update(MessageText);
// 
// 		if (existingMessageLength == 0)
// 		{
// 			new Effect.BlindDown(messageDOM);
// 		}
// 		else
// 		{
// 			new Effect.Shake(messageDOM);
// 		}
// 	}
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
