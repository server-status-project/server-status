function timeago()
{
	$("time.timeago").timeago();
	$("time.timeago").each(function(){
		var date = new Date($(this).attr("datetime"));
		$(this).attr("title",date.toLocaleString());
	});
}

(function(){
	timeago();

	var incidents = $('.timeline');
    $("body").on("click", "#loadmore", function(e){
    	e.preventDefault();
    	var url = $("#loadmore").attr("href") + "&ajax=true";
    	$("#loadmore").remove();

      	$.get(url, 
      		function(data){
       		incidents.append(data);
       		timeago();
     	});
    });
})();
    