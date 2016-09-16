define(function(require, exports, module) {

    var Store=require('store');

    exports.run = function() {

        if($('#liveCourseGuide').data('time')>0){
            var data = $('#liveCourseGuide').data();
            var time = parseInt(data.time/5);
            var courseId = data.courseId;
            var times = Store.get('live-course-guide-'+courseId);

            if(!times || $.inArray(time, times)<0){
                $('#liveCourseGuide').show();
                $('#liveCourseGuideModal').modal();
                $('#liveCourseGuideModal').on('hidden.bs.modal', function(e){
                    var courseId = data.courseId;
                    var times = Store.get('live-course-guide-'+courseId);
                    if(times){
                        if($.inArray(time, times)<0){
                            times[times.length] = time;
                            Store.set('live-course-guide-'+courseId, times);
                        }
                    } else {
                        Store.set('live-course-guide-'+courseId, [time]);
                    }
                })
            }
        }
    }

});