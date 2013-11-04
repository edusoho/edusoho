define(function(require, exports, module) {

    exports.run = function() {

        $("#theme-table").on('click', '.use-theme-btn', function(){
            if (!confirm('真的要使用该主题吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

    };

});