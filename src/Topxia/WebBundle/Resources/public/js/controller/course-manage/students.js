define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        require('./header').run();

        var $list = $("#course-student-list");

        $list.on('click', '.student-remove', function(){
            var $tr = $(this).parents('tr');
            var user_name = $('.student-remove').data('user') ;
            if (!confirm('您真的要移除该'+user_name+'吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(){
            	var user_name = $('.student-remove').data('user') ;
                Notify.success('移除'+user_name+'成功！');
                $tr.remove();
            }).error(function(){
            	var user_name = $('.student-remove').data('user') ;
                Notify.danger('移除'+user_name+'失败，请重试！');
            });
        });



        $("#course-student-list").on('click', '.follow-student-btn, .unfollow-student-btn', function() {
            
            var $this = $(this);

            $.post($this.data('url'), function(){
                $this.hide();
                if ($this.hasClass('follow-student-btn')) {
                    $this.parent().find('.unfollow-student-btn').show();
                } else {
                    $this.parent().find('.follow-student-btn').show();
                }
            });
            
        });


    }

});