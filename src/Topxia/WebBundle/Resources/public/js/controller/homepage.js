define(function(require, exports, module) {

    var $=require('jquery');
    require('jquery.slides');
    require('jquery.slides-css');

    exports.run = function() {

    	$("#photo .list-item").mouseenter(function(){
			$(this).find(".tip").addClass("i-active");
			//$(this).siblings().css("opacity", "0.6");
			$(this).find(".todos-thumb-span").fadeIn("fast");
			$(this).find(".todos-thumb-span").css("opacity", "0.6");
		});
		$("#photo .list-item").mouseleave(function(){
			$(this).find(".tip").removeClass("i-active");
			//$(this).siblings().css("opacity", "1");
			$(this).find(".todos-thumb-span").fadeOut("slow");
			$(this).find(".todos-thumb-span").css("opacity", "1");
		});



		 $(".course-grids .course-grid").mouseenter(function(){
	  
			$(this).find(".desc").css("opacity", "1");
			
		 });

		 $(".course-grids .course-grid").mouseleave(function(){
			
			$(this).find(".desc").css("opacity", "0");
			
		});
    	

    };

});