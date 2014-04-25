define(function(require, exports, module) {

    var $=require('jquery');
    require('jquery.slides');
    require('jquery.slides-css');

    require('wookmark');
    
    require('dialog-css');

   var  Dialog = require('dialog');

   new Dialog({
   		width:800,
   		content:"http://dev.osf.cn/page/dddddddd",
        zIndex:9999
    }).show();

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



		 $(".page-grids .card").mouseenter(function(){

		   $(this).find(".desc").css({display:"block"});
	  
			$(this).find(".desc").css("opacity", "1");
			$(this).find(".desc").css("filter", "alpha(opacity=50)");
		

			
		 });

		 $(".page-grids .card").mouseleave(function(){
			
			$(this).find(".desc").css("opacity", "0");
			$(this).find(".desc").css("filter", "alpha(opacity=0)");
			$(this).find(".desc").css({display:"none"});
			
		});




        // $(document).ready(function() {

        //     $("#grids li").wookmark({
        //         container: $("#grids"),
        //         offset: 0
        //     });

        // });

        // $(document).ready(function() {

        //     $("#teachers li").wookmark({
        //         container: $("#teachers"),
        //         offset: 0
        //     });

        // });
       

		
    	

    };

});