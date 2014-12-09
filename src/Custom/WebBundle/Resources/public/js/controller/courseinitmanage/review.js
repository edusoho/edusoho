define(function(require, exports, module) {

    exports.run = function() {

        $('.rating').on('click', '.delete-review', function() {
                var $tr = $(this).parents('tr');
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