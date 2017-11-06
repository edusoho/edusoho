define(function(require, exports, module) {

  var Class = require('class');

  var LocalStrategy = Class.extend({
    initialize: function(file, response) {
      file.gid = response.globalId;
      file.globalId = response.globalId;
      file.outerId = response.outerId;

      file.uploaderWidget.set('uploadToken', response.uploadToken);
      file.uploaderWidget.set('uploadUrl', response.uploadUrl);
      file.uploaderWidget.set('uploadProxyUrl', response.uploadProxyUrl);
      file.uploaderWidget.set('uploadMode', response.uploadMode);

      this.file = file;
      var uploaderWidget = file.uploaderWidget;

      uploaderWidget.uploader.option('server', response.uploadUrl + '/chunks');
      uploaderWidget.uploader.option('chunked', true);
      uploaderWidget.uploader.option('chunkSize', 1024 * 1024);


      var startUrl = uploaderWidget.get('uploadProxyUrl') + '/chunks/start';
      var postData = {
        file_gid: file.globalId,
        file_size: file.size,
        file_name: file.name
      };

      $.ajax(startUrl, {
        type: 'POST',
        data: postData,
        dataType: 'json',
        headers: {
          'Upload-Token': uploaderWidget.get('uploadToken')
        },
        success: function() {
          deferred.resolve();
        }
      });
    },

    uploadBeforeSend: function(object, data, headers) {
      var self = this.file.uploaderWidget;
      data.file_gid = object.file.gid;
      data.chunk_number = object.chunk + 1;
      headers['Upload-Token'] = self.get('uploadToken');
    },

    finishUpload: function(deferred) {
      var file = this.file;
      var xhr = $.ajax(file.uploaderWidget.get('uploadProxyUrl') + '/chunks/finish', {
        type: 'POST',
        data: {
          file_gid: file.gid
        },
        dataType: 'json',
        async: false,
        headers: {
          'Upload-Token': file.uploaderWidget.get('uploadToken')
        }
      });

      var result;
      xhr.done(function(data, textStatus, xhr) {
        result = data;
      });
      return result;
    },

    uploadAccept: function(object, ret) {

    },

    /**
     *
     * @param lastPercentage    上一次进度
     * @param currentPercentage 当前进度
     * @returns {boolean}
     */
    needDisplayPercent: function(lastPercentage, currentPercentage) {
      return lastPercentage === undefined || currentPercentage > lastPercentage;
    }
  });

  module.exports = Cloud2Strategy;
});