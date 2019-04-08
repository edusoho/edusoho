define(function(require, exports, module) {
  require('new-uploader');
  exports.run = function() {
    var $uploader = $('#uploader-container');
    var uploadProcess = {
      document: {
        type: 'html',
      },
    };
    var uploader = new UploaderSDK({
      id: $uploader.attr('id'),
      sdkBaseUri: app.cloudSdkBaseUri,
      disableDataUpload: app.cloudDisableLogReport,
      disableSentry: app.cloudDisableLogReport,
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      accept: $uploader.data('accept'),
      process: uploadProcess,
      fileSingleSizeLimit: $uploader.data('fileSingleSizeLimit'),
      ui: 'single'
    });

    uploader.on('file.finish', function(file) {
      if (file.length && file.length > 0) {
        var minute = parseInt(file.length / 60);
        var second = Math.round(file.length % 60);
        $("#minute").val(minute);
        $("#second").val(second);
        $("#length").val(minute * 60 + second);
      }

      var $metas = $('[data-role="metas"]');

      var currentTarget = $metas.data('currentTarget');
      var $ids = $('.' + $metas.data('idsClass'));
      var $list = $('.' + $metas.data('listClass'));

      if (currentTarget != '') {
        $ids = $('[data-role=' + currentTarget + ']').find('.' + $metas.data('idsClass'));
        $list = $('[data-role=' + currentTarget + ']').find('.' + $metas.data('listClass'));
      }

      $.get('/attachment/file/' + file.id + '/show', function(html) {
        $list.append(html);
        $ids.val(file.id);
        $('#attachment-modal').modal('hide');
        $list.siblings('.js-upload-file').hide();
      })
    });

    //只执行一次
    $('#attachment-modal').one('hide.bs.modal', function() {
      uploader.destroy();
      uploader = null;
    });
  }
});