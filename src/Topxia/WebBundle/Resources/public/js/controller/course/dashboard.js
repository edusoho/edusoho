define(function(require, exports, module) {

    exports.run = function() {
        require('./common').run();
        
        // if ($('#exit-course-learning').length > 0 ) {
        //     var $btn = $('#exit-course-learning');
        //     $btn.click(function(){

        //         if (!confirm('确定要退出学习？')) {
        //             return ;
        //         }

        //         var goto = $(this).data('goto');
        //         $.post($(this).data('url'), function(res){
        //             window.location.href = goto;
        //         });

        //     });
        // };
        
        $("#course-item-list").on('click', '.lesson-item', function(e) {
            window.location.href = $(this).find('.title').attr('href');
        });

        $('.js-exit-course').on('click', function(){
            var self = $(this);
            $.post($(this).data('url'), function(){
                window.location.href = self.data('go');
            });
        });

        function checkWidth(){
                if($(this).width()<400){
                $('.name').hide();         
                $('.course-item-list-wrap').css('margin-left','20px');
                $('.pie').css('margin-left','70px');
            }            
            if($(this).width()>=400){
                $('.name').show();
                $('.course-item-list-wrap').css('margin-left','99px');
                $('.pie').css('margin-left','0px');
            }
        }
        $('.announcement-list').on('click', '[data-role=delete]', function(){
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            }
            return false;
        });
        $(document).ready(function(){
           checkWidth();

        });
        $(window).resize(function(){         
           checkWidth();
        });

    };

});