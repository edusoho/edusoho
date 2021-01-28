define(function(require, exports, module) {

    exports.run = function() {

        var $form = $("#cloud-setting-form");

        var $info = $("#key-license-info")
        $.get($info.data('url'), function(html) {
            $("#loading-text").hide();
            $info.html(html);
            if ($info.find('.key-error-alert').length == 0) {
                $("#key-rest-btn").removeClass('hide');
            }
        });

        $info.on('click', '.key-bind-btn', function() {
            if (!confirm(Translator.trans('admin.setting.cloud_key.bind_hint'))) {
                return ;
            }
            $(this).button('loading');
            $.post($(this).data('url'), function(response) {

            }, 'json').done(function() {
                window.location.reload();
            });
        });

        $form.on('click', '.save-copyright-btn', function() {
            var $this = $(this);
            $this.button('loading');

            var params = {name: $('#field-copyrightName').val()};
            $.post($this.data('url'), params, function(){
                window.location.reload();
            });


            return false;
        });

    }

})