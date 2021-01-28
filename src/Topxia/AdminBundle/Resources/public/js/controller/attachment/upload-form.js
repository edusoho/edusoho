define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  exports.run = function() {
    $('.js-attachment-list').on('click', '.js-attachment-delete', function() {
      var $this = $(this);
      var attachment_remove = confirm(Translator.trans('admin.attachment.delete_hint'));
      if (attachment_remove) {
        $.post($this.data('url'), function(result) {

        }).done(function(result) {
          if (result.msg == 'ok') {
            Notify.success(Translator.trans('admin.attachment.delete_success_hint'));
            $this.closest('.js-attachment-list').parent().siblings('[data-role="fileId"]').val('');
            $this.closest('.js-attachment-list').parent().find('.js-upload-file').show();
            $this.closest('.js-attachment-list').children().remove();
          } else {
            Notify.danger(Translator.trans('admin.attachment.delete_failed_hint'));
          }
        }).fail(function(ajaxFailed) {
          Notify.danger(Translator.trans('admin.attachment.delete_failed_hint'));
        })
      }
    })
  }
});