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
            btn_flag = 1,
            default_limit = 10;
        $table = $('.task-table');
        $table_div = $('.task-table-div');
        $loading = $('<tr><td class="empty">.......正在载入数据.......</td><tr>');

        selectLesson();
        getFinishedLessonStudents(0, default_limit);

        function selectLesson() {
            $item = $('#carousel-lesson .carousel-inner').find('.active');
            $item.children('div:first').find('.thumbnail').toggleClass('lesson-selected');
            lessonId = $item.children('div:first').data('id');
        }
        function getFinishedLessonStudents(start, limit) {
            $table.find('tbody').fadeOut(function(){
                $table.find('tbody').html('');
                $table.find('tbody').append($loading).toggle();
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'finished',classId:$('#classSelecter').val()},function(html){
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
                $.get($table.data('url'),{lessonId:lessonId,start:start,limit:limit,type:'not-finished',classId:$('#classSelecter').val()},function(html){
                    var append = 0;
                    btn_flag = 0;
                    $loading.fadeOut(function(){
                        append++ == 0 && $table.find('tbody').toggle() && $table.find('tbody').append(html) && $table.find('tbody').fadeIn();
                    });
                });
            });
        }

        $('#not-finished-btn').on('click', function(){
            $table_div.addClass('fixed-height'); 
            getNotFinishedLessonStudents(0, default_limit);
            $(this).toggleClass('active');
            $('#finished-btn').toggleClass('active');
        });

        $('#finished-btn').on('click', function(){
            $table_div.addClass('fixed-height'); 
            getFinishedLessonStudents(0, default_limit);
            $(this).toggleClass('active');
            $('#not-finished-btn').toggleClass('active');
        }); 

        $('.carousel-inner').delegate('.thumbnail', 'click', function(){
            $('.carousel-inner').find('.lesson-selected').toggleClass('lesson-selected');
            $(this).toggleClass('lesson-selected');
            lessonId = $(this).parent().data('id');
            btn_flag ? getFinishedLessonStudents(0, default_limit) : getNotFinishedLessonStudents(0, default_limit);
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
                var type = btn_flag ? 'finished' : 'not-finished',
                    start = ($table.find('.has-item')).length;
                
                $.ajax({
                url: $table.data('url'),
                data:{lessonId:lessonId,start:start,limit:default_limit,type:type,classId:$('#classSelecter').val()},
                success: function(html)
                {
                    if($(html).hasClass('has-item'))
                    {
                        $table_div.removeClass('fixed-height');  
                        $table.find('tbody').append(html);
                    }
                }
                });

            }
        });
    }
});
