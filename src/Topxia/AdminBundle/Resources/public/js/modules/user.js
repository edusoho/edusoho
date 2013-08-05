define(function(require, exports, module) {
    var $ = require('jquery');

    exports.bootstrap = function(options) {
        $(function(options) {

            $('#sort-btn-groups button').click(function(){
                $(this).parents('.btn-group').find('[name=sort]').val($(this).data('value'));
                $('#user-search-form').submit();
            });

            $('.lock-user-btn').click(function(){
                if (!confirm('真的要禁止该用户登录吗？')) {
                    return ;
                }

                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            });

            $('.unlock-user-btn').click(function(){
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            });

        });
    };

});
