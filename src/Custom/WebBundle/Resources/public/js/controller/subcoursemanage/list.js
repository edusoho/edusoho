define(function(require, exports, module) {

    exports.run = function() {

        $('.sub-course-media').on('click', '.delete-btn', function() {
                var $li = $(this).parents('media');
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });

            });

        $('.course-publish-btn').click(function() {
            if (!confirm('您真的要发布该课程吗？')) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });

        });

    };

});