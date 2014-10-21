define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");

     exports.run = function() {
        $("#timePicker").datetimepicker({
            language: 'zh-CN',
            // autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month',
        });
        var lessonId = 0,
            btn_flag = 1;
        $table = $('.task-table');
        $loading = $('<tr><td class="empty">.......正在载入数据.......</td><tr>');

        selectLesson();
        getFinishedLessonStudents(0, 10);

        function selectLesson() {
            $item = $('#carousel-lesson .carousel-inner').find('.active');
            $item.children('div:first').find('.thumbnail').toggleClass('lesson-selected');
            lessonId = $item.children('div:first').data('id');
        }
        function getFinishedLessonStudents(start, limit) {
            $table.find('tbody').fadeOut(function(){
                $table.find('tbody').html('');
                $table.find('tbody').append($loading).toggle();
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'finished'},function(html){
                    var append = 0;
                    btn_flag = 1;
                    $loading.fadeOut(function(){
                        append++ == 0 && $table.find('tbody').toggle() && $table.find('tbody').append(html) && $table.find('tbody').fadeIn();
                    });
                });
            });
        }

        function getNotFinishedLessonStudents(start, limit) {
            $table.find('tbody').fadeOut(function(){
                $table.find('tbody').html('');
                $table.find('tbody').append($loading).toggle();
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'not-finished'},function(html){
                    var append = 0;
                    btn_flag = 0;
                    $loading.fadeOut(function(){
                        append++ == 0 && $table.find('tbody').toggle() && $table.find('tbody').append(html) && $table.find('tbody').fadeIn();
                    });
                });
            });
        }

        $('#not-finished-btn').on('click', function(){
            getNotFinishedLessonStudents(0, 10);
            $(this).toggleClass('active');
            $('#finished-btn').toggleClass('active');
        });

        $('#finished-btn').on('click', function(){
            getFinishedLessonStudents(0, 10);
            $(this).toggleClass('active');
            $('#not-finished-btn').toggleClass('active');
        }); 

        $('.carousel-inner').delegate('.thumbnail', 'click', function(){
            $('.carousel-inner').find('.lesson-selected').toggleClass('lesson-selected');
            $(this).toggleClass('lesson-selected');
            lessonId = $(this).parent().data('id');
            btn_flag ? getFinishedLessonStudents(0, 10) : getNotFinishedLessonStudents(0, 10);
        });

        $('#carousel-lesson').hover(function(){
            $(this).find('.carousel-control').toggle();
            },function(){
            $(this).find('.carousel-control').toggle();      
        });

        $('#timePicker, #classSelecter').on('change', function(){
            var date = $('#timePicker').val();
            var classId = $('#classSelecter').val();
            window.location = $(this).data('reload') + '?classId=' + classId + '&date=' + date;
        });
    }
});
