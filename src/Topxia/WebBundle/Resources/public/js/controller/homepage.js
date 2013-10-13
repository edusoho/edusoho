define(function(require, exports, module) {

    var $=require('jquery');
    require('jquery.slides');
    require('jquery.slides-css');

    exports.run = function() {

    	$("#photo .list-item").mouseenter(function(){
			$(this).find(".tip").addClass("i-active");
			$(this).siblings().css("opacity", "0.6");
			$(this).find(".todos-thumb-span").fadeIn("fast");
		});
		$("#photo .list-item").mouseleave(function(){
			$(this).find(".tip").removeClass("i-active");
			$(this).siblings().css("opacity", "1");
			$(this).find(".todos-thumb-span").fadeOut("slow");
		});


		 $(".course-grids .course-grid").mouseenter(function(){
	    	$(this).addClass("i-active");
			$(this).css("opacity", "0.6");
			
		 });

		 $(".course-grids .course-grid").mouseleave(function(){
			$(this).addClass("i-active");
			$(this).css("opacity", "1");
			
		});
    	

    };

});