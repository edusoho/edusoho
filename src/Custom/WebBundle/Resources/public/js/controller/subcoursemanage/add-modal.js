define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    
    exports.run = function() {

        $('.search-btn').on('click', function() {
           $.get($(this).data('url'), $('#search-form').serialize(), function(html){
                $('.modal-content').html($(html).find('.modal-content').html());
           });

        });

        $('.sub-course-media').on('click', '.choose-btn', function(){
            $.post($(this).data('url'), function(){
                var $modal = $('.sub-course-media').parents('.modal');
                $modal.modal('hide');
                Notify.success('添加课程成功');
                window.location.reload();
            }).error(function(){
                Notify.danger('添加课程失败');
            });;
        });

        $('.course-publish-btn').click(function() {
            if (!confirm('您真的要发布该课程吗？')) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });

        });

        $('#search-form').on("keyup keypress", function(e) {
          var code = e.keyCode || e.which; 
          if (code  == 13) {               
            e.preventDefault();
            return false;
          }
        });
    };

});