define(function (require, exports, module) {

  exports.run = function () {

    $("#theme-table").on('click', '.use-theme-btn', function () {
      var $btn = $(this);

      if ($btn.data('protocol') < 3) {
        alert(Translator.trans('admin.setting.theme.upgrade_error_hint'));
        return false;
      } else if (confirm(Translator.trans('admin.setting.theme.use_hint'))) {

        $.post($(this).data('url'), function (response) {
          if (response === false) {
            alert(Translator.trans('admin.setting.theme.upgrade_error_hint'));
          } else {
            window.location.reload();
          }
        });
      }
    });

  }

});