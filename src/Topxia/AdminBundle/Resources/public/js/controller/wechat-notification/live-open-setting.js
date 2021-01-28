define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('jquery.form');

  exports.run = function() {
    var $form = $('#notification-setting-form');
    $form.find('[name="scenes[]"]').on('change', function () {
      if ($form.find('[name="status"]:checked').val() === '1') {
        var flag = false;
        $form.find('[name="scenes[]"]').each(function (index, item) {
          if ($(item).is(':checked')) {
            flag = true;
          }
        });

        if (flag) {
          $('.js-scenes').find('.help-block').html('');
        }
        if (!flag) {
          $('.js-scenes').find('.help-block').html('<span class="color-danger">' + Translator.trans('admin.wechat.live_open_scenes_error.hint') + '</span>');
        }
      } else {
        $('.js-scenes').find('.help-block').html('');
      }
    });


    $('.js-notification-setting-btn').on('click', function() {
      if ($form.find('[name="status"]:checked').val() === '1') {
        var flag = false;
        $form.find('[name="scenes[]"]').each(function (index, item) {
          if ($(item).is(':checked')) {
            flag = true;
          }
        });

        if (!flag) {
          $('.js-scenes').find('.help-block').html('<span class="color-danger">' + Translator.trans('admin.wechat.live_open_scenes_error.hint') + '</span>');
          return;
        }
      } else {
        $('.js-scenes').find('.help-block').html('');
      }

      var $this = $(this);
      var url = $this.data('url');
      $this.button('loading');
      $.post(url, $form.serialize())
        .success(function(response) {
          window.location.reload();
        }).fail(function (xhr, status, error){
          $this.button('reset');
          Notify.danger(xhr.responseJSON.error.message);
        });
    });
  };

});