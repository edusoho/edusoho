define(function(require, exports, module) {

	var Class = require('class');

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
            var baiduParts = { parts: new Array()};

            $.ajax({
                url: self.get('uploadUrl')+ '?uploads',
                type:'POST',
                async: false,
                beforeSend: function(xhr){
                    headers = $.parseJSON(self.get('uploadToken'));
                    xhr.setRequestHeader("Authorization",  headers['Authorization']);
                    xhr.setRequestHeader("x-bce-date", headers['x-bce-date']);
                    
                },
                success:function(data) {
                    self.set('uploadId', data.uploadId);
                }
            });

            
            self.uploader.option('server', self.get('uploadUrl')+'?partNumber='+cloud2UploadStatus.currentChunkIndex+'&uploadId='+self.get('uploadId'));
            self.uploader.option('method', 'PUT');

	        self.set('cloud2UploadStatus',cloud2UploadStatus);
            self.set('baiduParts',baiduParts);

            file.uploaderWidget.uploader.option('chunked', true);
            file.uploaderWidget.uploader.option('chunkSize', cloud2UploadStatus.chunkSize);
            file.uploaderWidget.uploader.option('chunkRetry', 2);
            file.uploaderWidget.uploader.option('sendAsBinary', true);

        },

        uploadBeforeSend: function(object, data, headers){
            this._setChunkAuth();
        	var self = this.file.uploaderWidget;
        	$.each(data, function(i, n){
                delete data[i];
            })
            headers['x-bce-date'] = self.get('bceDate');
            headers['Authorization'] = self.get('chunkAuth');
        },

        _initCloud2UploadStatus: function(){
            return {
                chunkSize: 1024*1024,
                currentChunkIndex: 1
            };
        },

        _setChunkAuth: function(){
            var self = this.file.uploaderWidget;
            var cloud2UploadStatus = self.get('cloud2UploadStatus');
            var encryptParams = {
                "partNumber" : cloud2UploadStatus.currentChunkIndex,
                "uploadId" : self.get('uploadId')
            }
            var result = this._getUploadAuth(encryptParams, 'PUT');
            self.set('chunkAuth', result['Authorization']);
            self.set('bceDate', result['x-bce-date']);
        },

        _getUploadAuth: function(encryptParams, httpMethod){
            var self = this.file.uploaderWidget;
            var result;
            $.ajax({
                url: self.get('uploadAuthUrl'),
                type:'POST',
                data:{
                        "encryptParams" : encryptParams,
                        "globalId" : this.file.gid,
                        "httpMethod" : httpMethod
                    },
                async: false,
                success:function(data) {
                    result = data;
                }
            });
            return result;
        },

        finishUpload: function(deferred) {
            var self = this.file.uploaderWidget;
            var baiduParts = self.get('baiduParts');
            var uploadId = self.get('uploadId');
            var url = self.get('uploadUrl')+'?uploadId='+uploadId;
            encryptParams = {
                "uploadId":uploadId
            };
            headers = this._getUploadAuth(encryptParams, "POST");
            var result = {};

            $.ajax({
                url: url,
                type:'POST',
                dataType:'json',
                async: false,
                data:JSON.stringify(baiduParts),
                beforeSend: function(xhr){
                    xhr.setRequestHeader("Authorization", headers['Authorization']);
                    xhr.setRequestHeader("x-bce-date", headers['x-bce-date']);
                },
                success:function(data) {
                    result = data;
                }
            });
            return $.extend({id: this.file.fileId}, result);
        },

        uploadAccept: function(object, ret){
        	var self = this.file.uploaderWidget;
        	var cloud2UploadStatus = self.get('cloud2UploadStatus');

            if (ret._xhr instanceof XMLHttpRequest && ret._xhr.getResponseHeader('ETag')) {
                var baiduParts = self.get('baiduParts');
                baiduParts.parts.push({partNumber:cloud2UploadStatus.currentChunkIndex, eTag : ret._xhr.getResponseHeader('ETag').replace(/\"/g, '')}); 
                self.set('baiduParts', baiduParts);
            }

            cloud2UploadStatus.currentChunkIndex++;
            self.uploader.option('server', self.get('uploadUrl')+'?partNumber='+cloud2UploadStatus.currentChunkIndex+'&uploadId='+self.get('uploadId'));
            self.uploader.option('method', 'PUT');
            self.set('cloud2UploadStatus', cloud2UploadStatus);

        }
    });

    module.exports = Cloud2Strategy;
});