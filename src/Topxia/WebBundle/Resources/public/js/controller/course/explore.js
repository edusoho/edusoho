define(function(require, exports, module) {

    var Lazyload = require('echo.js');


    exports.run = function() {
        Lazyload.init();
        $('#live, #free').on('click', function(event) {
        	$('input:checkbox').attr('checked',false);
        	$(this).attr('checked',true);

        	window.location.href = $(this).val();
        });


    	$(".open-course-list").on('click','.section-more-btn a', function(){
      	var url = $(this).attr('data-url');
	      	$.ajax({
		        url: url,
		        dataType: 'html',
		        success: function(html) {
	          	var html = $('.open-course-list .course-block,.open-course-list .section-more-btn', $(html)).fadeIn('slow');
		        $(".section-more-btn").remove();
		        $('.open-course-list').append(html);
                Lazyload.init();
		        } 
	      	});
	    });
    };

});