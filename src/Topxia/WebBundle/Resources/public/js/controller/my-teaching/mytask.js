define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");

     exports.run = function() {
        $("#timePicker").datetimepicker({
            language: 'zh-CN',
            // autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
        var lessonId = 0,
            default_limit = 10;
        $table = $('.task-table');
        $table_div = $('.task-table-div');

        selectLesson();
        getFinishedLessonStudents(0, default_limit);

        function selectLesson() {
            $item = $('#carousel-lesson .carousel-inner').find('.active');
            $item.children('div:first').find('.thumbnail').toggleClass('lesson-selected');
            lessonId = $item.children('div:first').data('id');
        }
        function getFinishedLessonStudents(start, limit) {
            $table.find('tbody').fadeOut(function(){   
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'finished',classId:$('#classSelecter').val()},function(html){
                    
                    $table.find('tbody').html('') && $table.find('tbody').append(html) && $table.find('tbody').fadeIn();
                });
            });
        }

        function getNotFinishedLessonStudents(start, limit) {
            $table.find('tbody').fadeOut(function(){
                $table.find('tbody').html('');
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'not-finished',classId:$('#classSelecter').val()},function(html){
                    $table.find('tbody').html('') && $table.find('tbody').append(html) && $table.find('tbody').fadeIn();
                });
            });
        }

        $('#not-finished-btn').on('click', function(){
            getNotFinishedLessonStudents(0, default_limit);
            $(this).addClass('active');
            $('#finished-btn').removeClass('active');
        });

        $('#finished-btn').on('click', function(){
            getFinishedLessonStudents(0, default_limit);
            $(this).addClass('active');
            $('#not-finished-btn').removeClass('active');
        }); 

        $('.carousel-inner').delegate('.thumbnail', 'click', function(){
            $('.carousel-inner').find('.lesson-selected').toggleClass('lesson-selected');
            $(this).toggleClass('lesson-selected');
            lessonId = $(this).parent().data('id');
            ($('.tasks-body .nav li.active').attr('id') == 'finished-btn') ? getFinishedLessonStudents(0, default_limit) : getNotFinishedLessonStudents(0, default_limit);
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

        $(window).scroll(function()
        {
            if($(window).scrollTop() == $(document).height() - $(window).height())
            {
                var type = ($('.tasks-body .nav li.active').attr('id') == 'finished-btn') ? 'finished' : 'not-finished',
                    start = ($table.find('.has-item')).length;
                
                start%default_limit==0 && $.ajax({
                url: $table.data('url'),
                data:{lessonId:lessonId,start:start,limit:default_limit,type:type,classId:$('#classSelecter').val()},
                success: function(html)
                {
                    if($(html).hasClass('has-item'))
                    {
                        $table.find('tbody').append(html);
                    }
                }
                });

            }
        });
    }
});
