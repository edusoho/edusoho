define(function (require, exports, module) {

  var WebUploader = require('edusoho.webuploader');
  var Notify = require('common/bootstrap-notify');
  var Uploader = require('upload');

  exports.run = function () {
    var $form = $('#business-form');

    var uploaderRecord = new WebUploader({
      element: '#record-picture-upload'
    });

    uploaderRecord.on('uploadSuccess', function (file, response) {
      var url = $('#record-picture-upload').data('gotoUrl');

      $.post(url, response, function (data) {
        $('#record-picture-container').html('<img src="' + data.url + '" style="margin-bottom: 10px;">');
        $form.find('[name=recordPicture]').val(data.path);
        $('#record-picture-remove').show();
        Notify.success(Translator.trans('admin.site.upload_record_picture_success_hint'));
      });
    });

    $('#record-picture-remove').on('click', function () {
      if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
      var $btn = $(this);
      var $recordContainer = $('#record-picture-container');
      $recordContainer.html('');
      $recordContainer.append('<img src="/assets/img/default/gongan.png">');
      $form.find('[name=recordPicture]').val('');
      $btn.hide();
    });

    $('#save-site').on('click', function () {
      $.post($form.data('saveUrl'), $form.serialize(), function (data) {
        Notify.success(data.message);
      });
    });
  };

});
