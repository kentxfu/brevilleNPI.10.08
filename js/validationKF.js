
//jQuery(document).ready(function($){

	
	function validation(thisForm)
	{
		var valid = true;
		
		//alert(thisForm);
		
		$("#" + thisForm + " .required").each(function(){
			if ($(this).val() == "" || $(this).val() == "--Please Select--")
			{
				$(this).css("borderColor","red");	
				if($(this).next("span").length <= 0){
					var msg = (typeof $(this).attr("data-errorMsg") === "undefined") ? "" : $(this).attr("data-errorMsg");
					
					//$(this).after("<span class='alert alert-danger'>" + msg + "</span>");
				}
				valid = false;
			}
			else
			{
				$(this).css("borderColor","#D3D3D3");
				$(this).next("span").remove();
			}
		});

		$("#" + thisForm + " .regex").each(function(){
			var regex = new RegExp( $(this).attr("data-regex") );

			if (!regex.test($(this).val()))
			{
				$(this).css("borderColor","red");
				if($(this).next("span").length <= 0)
				{
					$(this).after(" <span class='alert alert-danger'>" + $(this).attr("data-errorMsg") + "</span>");
				}
				valid = false;
			}
			else
			{
				$(this).css("borderColor","#D3D3D3");
				$(this).next("span").remove();
			}
		});
		
		if(valid)
		{
			return true;
		}
		else
			return false;
	}
	
//});