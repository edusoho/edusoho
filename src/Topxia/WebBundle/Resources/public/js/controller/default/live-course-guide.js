define(function(require, exports, module) {

    var Store=require('store');

    exports.run = function() {

        if($('#liveCourseGuide').data('time')>0){
            var data = $('#liveCourseGuide').data();
            var time = parseInt(data.time/5);
            var lessonId = data.lessonId;
            var times = Store.get('live-course-guide-'+lessonId);

            if(!times || $.inArray(time, times)<0){
                $('#liveCourseGuide').show();
                $('#liveCourseGuideModal').modal();
                $('#liveCourseGuideModal').on('hidden.bs.modal', function(e){
                    var lessonId = data.lessonId;
                    var times = Store.get('live-course-guide-'+lessonId);
                    if(times){
                        if($.inArray(time, times)<0){
                            times[times.length] = time;
                            Store.set('live-course-guide-'+lessonId, times);
                        }
                    } else {
                        Store.set('live-course-guide-'+lessonId, [time]);
                    }
                })
            }
        }
    }

});