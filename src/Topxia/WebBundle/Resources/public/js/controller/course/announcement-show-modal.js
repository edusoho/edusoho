define(function(require, exports, module) {
    exports.run = function() {

        $('.announcement-detail').on('click', '[data-role=delete]', function(){
            if (confirm('真的要删除该公告吗？')) {
                $.post($(this).data('url'), function(){
                    window.location.reload();
                });
            }
            return false;
        });

    };
});