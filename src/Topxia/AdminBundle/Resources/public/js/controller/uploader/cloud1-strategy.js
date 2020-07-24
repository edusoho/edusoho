define(function(require, exports, module) {

  var Class = require('class');
  var store = require('store');

  var Cloud2Strategy = Class.extend({
    initialize: function(file, response) {

      file.gid = response.globalId;
      file.globalId = response.globalId;
      file.fileId = response.outerId;

      file.uploaderWidget.set('uploadToken', response.uploadToken);
      file.uploaderWidget.set('uploadUrl', response.uploadUrl);
      file.uploaderWidget.set('uploadProxyUrl', response.uploadProxyUrl);
      file.uploaderWidget.set('uploadMode', response.uploadMode);

      this.file = file;
      var self = file.uploaderWidget;
      var cloud2UploadStatus = this._initCloud2UploadStatus();

      cloud2UploadStatus.currentFileSize = file.size;
      cloud2UploadStatus.blockIndex = Math.ceil(cloud2UploadStatus.currentFileSize / cloud2UploadStatus.blockSize);
      cloud2UploadStatus.chunkIndex = Math.ceil(cloud2UploadStatus.currentFileSize / cloud2UploadStatus.chunkSize);
      if (cloud2UploadStatus.blockSize > cloud2UploadStatus.currentFileSize) {
        file.uploaderWidget.uploader.option('server', self.get('uploadUrl') + '/mkblk/' + cloud2UploadStatus.currentFileSize);
      } else {
        file.uploaderWidget.uploader.option('server', self.get('uploadUrl') + '/mkblk/' + cloud2UploadStatus.blockSize);
      }
      self.set('cloud2UploadStatus', cloud2UploadStatus);

      file.uploaderWidget.uploader.option('chunked', true);
      file.uploaderWidget.uploader.option('chunkSize', cloud2UploadStatus.chunkSize);
      file.uploaderWidget.uploader.option('sendAsBinary', true);

    },

    uploadBeforeSend: function(object, data, headers) {
      var self = this.file.uploaderWidget;
      $.each(data, function(i, n) {
        delete data[i];
      })
      headers.Authorization = "UpToken " + self.get('uploadToken');
    },

    _initCloud2UploadStatus: function() {
      return {
        ctxs: new Array(),
        currentFileSize: 0,
        blockSize: 4 * 1024 * 1024,
        chunkSize: 1024 * 1024,
        blockIndex: 0,
        currentBlockIndex: 1,
        chunkIndex: 0,
        currentChunkIndex: 0
      };
    },

    finishUpload: function(deferred) {
      var self = this.file.uploaderWidget;
      var cloud2UploadStatus = self.get('cloud2UploadStatus');
      var hashId = this.base64encode(this.utf16to8(this.file.initResponse.reskey));
      var url = self.get('uploadUrl') + '/mkfile/' + cloud2UploadStatus.currentFileSize + '/key/' + hashId;
      var result = {};

      var xhr = new XMLHttpRequest();
      xhr.open('POST', url, false);
      xhr.setRequestHeader("Authorization", "UpToken " + self.get('uploadToken'));
      xhr.onreadystatechange = function(response) {
        if (xhr.readyState == 4 && xhr.status == 200 && response != "") {
          result = JSON.parse(xhr.responseText);
        }
      };
      xhr.send(cloud2UploadStatus.ctxs.join(','));
      return $.extend({
        id: this.file.fileId
      }, result);
    },

    base64encode: function(str) {
      var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";

      var out, i, len;
      var c1, c2, c3;
      len = str.length;
      i = 0;
      out = "";
      while (i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == len) {
          out += base64EncodeChars.charAt(c1 >> 2);
          out += base64EncodeChars.charAt((c1 & 0x3) << 4);
          out += "==";
          break;
        }
        c2 = str.charCodeAt(i++);
        if (i == len) {
          out += base64EncodeChars.charAt(c1 >> 2);
          out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
          out += base64EncodeChars.charAt((c2 & 0xF) << 2);
          out += "=";
          break;
        }
        c3 = str.charCodeAt(i++);
        out += base64EncodeChars.charAt(c1 >> 2);
        out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
        out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
        out += base64EncodeChars.charAt(c3 & 0x3F);
      }
      return out;
    },

    utf16to8: function(str) {
      var out, i, len, c;
      out = "";
      len = str.length;
      for (i = 0; i < len; i++) {
        c = str.charCodeAt(i);
        if ((c >= 0x0001) && (c <= 0x007F)) {
          out += str.charAt(i);
        } else if (c > 0x07FF) {
          out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
          out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
          out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        } else {
          out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
          out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        }
      }
      return out;
    },

    uploadAccept: function(object, ret) {
      var self = this.file.uploaderWidget;
      var cloud2UploadStatus = self.get('cloud2UploadStatus');
      cloud2UploadStatus.currentChunkIndex++;
      if (cloud2UploadStatus.currentChunkIndex % 4 == 0) {
        cloud2UploadStatus.currentBlockIndex++;
        if (cloud2UploadStatus.currentBlockIndex < cloud2UploadStatus.blockIndex) {
          self.uploader.option('server', self.get('uploadUrl') + '/mkblk/' + cloud2UploadStatus.blockSize);
        } else if (cloud2UploadStatus.currentBlockIndex == cloud2UploadStatus.blockIndex) {
          self.uploader.option('server', self.get('uploadUrl') + '/mkblk/' + (cloud2UploadStatus.currentFileSize % cloud2UploadStatus.blockSize));
        }
      } else {
        var offsetSize = cloud2UploadStatus.currentChunkIndex % 4 * cloud2UploadStatus.chunkSize;
        self.uploader.option('server', self.get('uploadUrl') + '/bput/' + ret.ctx + '/' + offsetSize);
      }
      if (cloud2UploadStatus.currentChunkIndex % 4 == 0 || cloud2UploadStatus.chunkIndex == cloud2UploadStatus.currentChunkIndex) {
        cloud2UploadStatus.ctxs.push(ret.ctx);
      }
      self.set('cloud2UploadStatus', cloud2UploadStatus);
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