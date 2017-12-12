(function(){
	$("#time_input").flatpickr({enableTime:true, minDate: "today",time_24hr:true, formatDate: function(date, format) {
    	return date.toISOString();
	}});
	$("#end_time").flatpickr({enableTime:true, minDate: "today",time_24hr:true, formatDate: function(date, format) {
	    return date.toISOString(); // iso date str
	}});


	var classes = ["panel panel-danger", "panel panel-warning", "panel panel-primary", "panel panel-success"];
	var icons = ["fa fa-times", "fa fa-exclamation", "fa fa-info", "fa fa-check"];

	$("body").on("change","#new-incident select", function(){
		var val = $(this).val();

		$("#new-incident .panel.new .panel-heading i").get(0).className = icons[val];
		$("#new-incident .panel.new").get(0).className = classes[val] + " new";
	});

	$("#new-incident select").trigger("change");

	$("body").on("submit","#new-incident",function(){
		var time = Date.parse($('#time').val());
		var end_time = Date.parse($('#end_time').val());

		if (time>end_time|| isNaN(time) || isNaN(end_time))
		{
			//TODO: Error class
			$('#time').addClass("error");
			$('#end_time').addClass("error");
			return false;	
		}

		if($('#status-container :checkbox:checked').length == 0)
		{
			$('#status-container').addClass("error");
			return false;
		}
	});
})();
    
