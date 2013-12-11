define(function(require, exports, module) {

	require('wookmark');
    
    exports.run = function() {

       $('#teacher-carousel').carousel({interval: 0});
        $('#teacher-carousel').on('slide.bs.carousel', function (e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item').removeClass('teacher-item-active');
            $('#teacher-detail').find('.teacher-item-' + teacherId).addClass('teacher-item-active');
        });


        $("#last-list li").wookmark({ 
		    container:$("#last-list"), 
		    offset:0
		  	  
		}); 



         $(".grid-img").mouseenter(function(){

           $(this).find(".card-desc").css({display:"block"});
      
            $(this).find(".card-desc").css("opacity", "1");
            $(this).find(".card-desc").css("filter", "alpha(opacity=50)");
        

            
         });

         $(".grid-img").mouseleave(function(){
            
            $(this).find(".card-desc").css("opacity", "0");
            $(this).find(".card-desc").css("filter", "alpha(opacity=0)");
            $(this).find(".card-desc").css({display:"none"});
            
        });




    }

});