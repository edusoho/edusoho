define(function(require, exports, module) {

    exports.run = function() {

        $(".uninstall-btn").click(function() {
            if (!confirm(Translator.trans('admin.app.uninstall_hint'))) {
                return ;
            }
            $.post($(this).data('url'), function(response) {
                window.location.reload();
            }, 'json');
        });
        let $url = $('.js-table').data('url');
        $.post($url, function(count){

            if (count > 0) {

                $('.app-upgrade').append("<span class=\"badge mls\" style=\"background-color:#FF3333\">"+count+"</span>");
            }

        });

    };

});