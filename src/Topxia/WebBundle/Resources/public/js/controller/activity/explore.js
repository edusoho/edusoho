define(function(require, exports, module) {
    
    exports.run = function() {

       $('#teacher-carousel').carousel({interval: 0});
        $('#teacher-carousel').on('slide.bs.carousel', function (e) {
            var teacherId = $(e.relatedTarget).data('id');

            $('#teacher-detail').find('.teacher-item').removeClass('teacher-item-active');
            $('#teacher-detail').find('.teacher-item-' + teacherId).addClass('teacher-item-active');
        });


    }

});