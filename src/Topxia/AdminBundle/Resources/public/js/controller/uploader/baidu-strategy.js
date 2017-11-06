define(function(require, exports, module) {

  var Class = require('class');

  var Cloud2Strategy = Class.extend({
    initialize: function(file, response) {
      file.gid = response.globalId;
      file.globalId = response.globalId;
      file.fileId = response.outerId;
      file.uploadUrl = response.uploadUrl;
      file.uploadProxyUrl = response.uploadProxyUrl;
      file.uploadMode = response.uploadMode;
      file.uploadId = response.uploadToken;

      var baiduParts = {
        parts: new Array()
      };
      file.baiduParts = baiduParts;

      if (file.initResponse && file.initResponse.uploadToken) {
        file.uploadId = file.initResponse.uploadToken;
      }

      var self = file.uploaderWidget;

      self.uploader.option('method', 'POST');
      self.uploader.option('chunked', true);
      self.uploader.option('chunkSize', 1024 * 1024 * 5);
      self.uploader.option('sendAsBinary', true);
      self.uploader.option('threads', 5);

    },

    uploadBeforeSend: function(object, data, headers, tr) {
      var uploadAuthUrl = object.file.uploaderWidget.get('uploadAuthUrl');
      var partNumber = object.chunk + 1;
      var encryptParams = {
        "partNumber": partNumber,
        "uploadId": object.file.uploadId
      }
      var authResult = this._getUploadAuth(encryptParams, 'POST', uploadAuthUrl, object.file.gid);

      headers['x-bce-date'] = authResult['x-bce-date'];
      headers['Authorization'] = authResult['Authorization'];

      $.each(data, function(i, n) {
        delete data[i];
      });

      tr.options.server = object.file.uploadUrl + '?partNumber=' + partNumber + '&uploadId=' + object.file.uploadId;
    },

    _getUploadAuth: function(encryptParams, httpMethod, uploadAuthUrl, gid) {
      var result;
      $.ajax({
        url: uploadAuthUrl,
        type: 'POST',
        data: {
          "encryptParams": encryptParams,
          "globalId": gid,
          "httpMethod": httpMethod
        },
        async: false,
        success: function(data) {
          result = data;
        }
      });
      return result;
    },

    _getParameterByName: function(name, url) {
      var parser = document.createElement('a');
      parser.href = url;
      var query = parser.search.substring(1);
      var vars = query.split('&');
      for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == name) {
          return decodeURIComponent(pair[1]);
        }
      }
    },

    _getJsonKey: function(data, key) {
      var realKey;
      $.each(data, function(index, val) {
        if (key.toLowerCase() == index.toLowerCase()) {
          realKey = index;
        }
      });
      return realKey;
    },

    finishUpload: function(deferred, file) {
      var uploadAuthUrl = file.uploaderWidget.get('uploadAuthUrl');
      var baiduParts = file.baiduParts;
      baiduParts.parts = baiduParts.parts.sort(function(a, b) {
        return (a['partNumber'] - b['partNumber']);
      });
      var uploadId = file.uploadId;
      var url = file.uploadUrl + '?uploadId=' + uploadId;
      encryptParams = {
        "uploadId": uploadId
      };
      headers = this._getUploadAuth(encryptParams, "POST", uploadAuthUrl, file.gid);
      var result = {};

      $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        async: false,
        data: JSON.stringify(baiduParts),
        beforeSend: function(xhr) {
          xhr.setRequestHeader("Authorization", headers['Authorization']);
          xhr.setRequestHeader("x-bce-date", headers['x-bce-date']);
        },
        success: function(data) {
          result = data;
        }
      });
      return $.extend({
        id: file.fileId
      }, result);
    },

    uploadAccept: function(object, ret) {
      var etagKey = this._getJsonKey(ret._responseHeaders, 'etag');
      if (etagKey !== undefined) {
        var partNumber = this._getParameterByName('partNumber', ret._requestURL);
        object.file.baiduParts.parts.push({
          partNumber: parseInt(partNumber),
          eTag: ret._responseHeaders[etagKey].replace(/\"/g, '')
        });
      }
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