define(function(require, exports, module) {

    exports.run = function() {

        $(".uninstall-btn").click(function() {
            if (!confirm('真的要卸载此应用？')) {
                return ;
            }
            $.post($(this).data('url'), function(response) {
                window.location.reload();
            }, 'json');
        });

    };

});