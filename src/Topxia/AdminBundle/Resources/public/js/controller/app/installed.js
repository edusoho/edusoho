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

        $.post('/admin/app/upgrades_count', function(count){

            if (count > 0) {

                $('.app-upgrade').append("<span class=\"badge mls\" style=\"background-color:#FF3333\">"+count+"</span>");
            }

        });

    };

});