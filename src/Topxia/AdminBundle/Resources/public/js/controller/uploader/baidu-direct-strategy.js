define(function(require, exports, module) {

  var Class = require('class');

  var BaiduDirectStrategy = Class.extend({
    initialize: function(file, response) {
      file.gid = response.globalId;
      file.globalId = response.globalId;
      file.fileId = response.outerId;
      file.uploaderWidget.set('uploadUrl', response.uploadUrl);
      file.uploaderWidget.set('uploadProxyUrl', response.uploadProxyUrl);
      file.uploaderWidget.set('uploadMode', response.uploadMode);

      this.file = file;
      var self = file.uploaderWidget;

      self.uploader.option('server', self.get('uploadUrl'));
      self.uploader.option('method', 'POST');

      file.uploaderWidget.uploader.option('chunked', false);
      file.uploaderWidget.uploader.option('sendAsBinary', true);

    },

    uploadBeforeSend: function(object, data, headers) {
      this._setAuth();
      var self = this.file.uploaderWidget;
      $.each(data, function(i, n) {
        delete data[i];
      })
      headers['x-bce-date'] = self.get('bceDate');
      headers['Authorization'] = self.get('uploadAuth');
    },

    _setAuth: function() {
      var self = this.file.uploaderWidget;
      var encryptParams = {}
      var result = this._getUploadAuth(encryptParams, 'POST');
      self.set('uploadAuth', result['Authorization']);
      self.set('bceDate', result['x-bce-date']);
    },

    _getUploadAuth: function(encryptParams, httpMethod) {
      var self = this.file.uploaderWidget;
      var result;
      $.ajax({
        url: self.get('uploadAuthUrl'),
        type: 'POST',
        data: {
          "encryptParams": encryptParams,
          "globalId": this.file.gid,
          "httpMethod": httpMethod
        },
        async: false,
        success: function(data) {
          result = data;
        }
      });
      return result;
    },

    finishUpload: function(deferred) {
      return {
        id: this.file.fileId,
        status: 'success'
      };
    },

    uploadAccept: function(object, ret) {

    },

    needDisplayPercent: function(lastPercentage, currentPercentage) {
      return lastPercentage === undefined || currentPercentage > lastPercentage;
    }
  });

  module.exports = BaiduDirectStrategy;
});