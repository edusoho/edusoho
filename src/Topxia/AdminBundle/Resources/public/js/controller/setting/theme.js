define(function (require, exports, module) {

  exports.run = function () {

    $("#theme-table").on('click', '.use-theme-btn', function () {
      var $btn = $(this);

      if ($btn.data('protocol') < 3) {
        alert('该主题暂不适配当前ES版本，请先升级');
        return false;
      } else if (confirm(Translator.trans('真的要使用该主题吗？'))) {

        $.post($(this).data('url'), function (response) {
          if (response === false) {
            alert('该主题暂不适配当前ES版本，请先升级');
          } else {
            window.location.reload();
          }
        });
      }
    });

  }

});