define(function(require, exports, module) {

    exports.run = function() {

        var $info = $("#key-license-info")
        $.get($info.data('url'), function(html) {
            $("#loading-text").hide();
            $info.html(html);
            if ($info.find('.key-error-alert').length == 0) {
                $("#key-rest-btn").removeClass('hide');
            }
        });

        $info.on('click', '.key-bind-btn', function() {
            if (!confirm('授权域名一旦绑定就无法变更，您真的要绑定该授权域名吗？')) {
                return ;
            }
            $(this).button('loading');
            $.post($(this).data('url'), function(response) {

            }, 'json').done(function() {
                window.location.reload();
            });
        });


    }

})