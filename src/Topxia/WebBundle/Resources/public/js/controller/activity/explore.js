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




    }

});