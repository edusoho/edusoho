define(function(require, exports, module) {

  var Class = require('class');

  var LocalStrategy = Class.extend({
    initialize: function(file, response) {
      this.file = file;
      file.uploaderWidget.uploader.option('server', response.url);
      file.uploaderWidget.uploader.option('formData', response.postParams);
    },

    uploadBeforeSend: function(object, data, headers) {
      $.each(data, function(i, n) {
        if (i != 'file') {
          delete data[i];
        }
      })
    },

    uploadAccept: function(object, ret) {
      this.file.fileId = ret.id;
    },

    finishUpload: function(deferred) {
      return {
        id: this.file.fileId,
        status: 'success'
      };
    },
    /**
     *
     * @param lastPercentage    上一次进度
     * @param currentPercentage 当前进度
     * @returns {boolean}
     */
    needDisplayPercent: function(lastPercentage, currentPercentage) {
      return true;
    }
  });

  module.exports = LocalStrategy;
});