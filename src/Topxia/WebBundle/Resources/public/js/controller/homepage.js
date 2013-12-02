define(function(require, exports, module) {

    var $=require('jquery');
    require('jquery.slides');
    require('jquery.slides-css');

    exports.run = function() {

    	$("#photo .list-item-user").mouseenter(function(){

			$(this).find(".tip").addClass("i-active");			

			if($("#wait-you").offset().left-$(this).offset().left > 150){
				$(this).find(".todos-thumb-span").addClass("todos-thumb-span-left");
				$(this).find(".todos-thumb-span").addClass("text-left");

			}else{
				$(this).find(".todos-thumb-span").addClass("todos-thumb-span-right");
				$(this).find(".todos-thumb-span").addClass("text-right");
			}
			$(this).find(".todos-thumb-span").css({width:"0px"});	
			$(this).find(".todos-thumb-span").css({display:"block"});
			$(this).find(".todos-thumb-span").animate({width:"157px"},300);
			
		
		});
		$("#photo .list-item-user").mouseleave(function(){
			$(this).find(".tip").removeClass("i-active");
			if($("#wait-you").offset().left-$(this).offset().left > 150){
				$(this).find(".todos-thumb-span").removeClass("todos-thumb-span-left");
				$(this).find(".todos-thumb-span").removeClass("text-left");
			}else{
				$(this).find(".todos-thumb-span").removeClass("todos-thumb-span-right");
				$(this).find(".todos-thumb-span").removeClass("text-right");
			}
			$(this).find(".todos-thumb-span").css({display:'none'});
		});



		 $(".course-grids .course-grid").mouseenter(function(){

		   $(this).find(".desc").css({display:"block"});
	  
			$(this).find(".desc").css("opacity", "1");
			$(this).find(".desc").css("filter", "alpha(opacity=50)");
		

			
		 });

		 $(".course-grids .course-grid").mouseleave(function(){
			
			$(this).find(".desc").css("opacity", "0");
			$(this).find(".desc").css("filter", "alpha(opacity=0)");
			$(this).find(".desc").css({display:"none"});
			
		});

		$('#teacher-carousel').carousel({interval: 0});
        $('#teacher-carousel').on('slide.bs.carousel', function (e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item-user').removeClass('teacher-item-user-active');
            $('#teacher-detail').find('.teacher-item-user-' + teacherId).addClass('teacher-item-user-active');
        });
    	

    };

});