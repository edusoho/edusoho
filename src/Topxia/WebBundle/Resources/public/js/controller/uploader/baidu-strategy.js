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
                // dataType:'json',
                async: false,
                data:baiduParts,
                beforeSend: function(xhr){
                    // xhr.setRequestHeader("Content-Type", "application/json");
                    
                    headers = $.parseJSON(self.get('uploadToken'));
                    console.log(headers);
                    $.each(headers, function(index, val) {
                         // console.log(index);
                         // console.log(val);

                         xhr.setRequestHeader(index, val);
                    });
                    // xhr.setRequestHeader("Accept", "");
                    // xhr.setRequestHeader("Accept-Encoding", "");
                    // xhr.setRequestHeader("Accept-Language", "");
                    // xhr.setRequestHeader("Cache-Control", "");
                    // xhr.setRequestHeader("Connection", "");
                    // xhr.setRequestHeader("Origin", "");
                    // xhr.setRequestHeader("Pragma", "");
                    // xhr.setRequestHeader("Referer", "");
                    // xhr.setRequestHeader("User-Agent", "");
                    // xhr.setRequestHeader("X-CSRF-Token", "");
                    // xhr.setRequestHeader("Origin", "");
                    // xhr.setRequestHeader("Authorization", headers['Authorization']);
                    // xhr.setRequestHeader("x-bce-date", headers['x-bce-date']);
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

        	var self = this.file.uploaderWidget;
        	$.each(data, function(i, n){
                delete data[i];
            })
            // headers.Authorization = "UpToken "+self.get('uploadToken');
            // console.log(headers);
        },

        _initCloud2UploadStatus: function(){
            return {
                chunkSize: 1024*1024,
                currentChunkIndex: 1
            };
        },

        finishUpload: function(deferred) {
            var self = this.file.uploaderWidget;
            var baiduParts = self.get('baiduParts');
            var uploadId = self.get('uploadId');
            var url = self.get('uploadUrl')+'?uploadId='+uploadId;
            var result = {};
            $.ajax({
                url: url,
                type:'POST',
                dataType:'json',
                async: false,
                data:JSON.stringify(baiduParts),
                beforeSend: function(xhr){
                    xhr.setRequestHeader("Content-Type", "application/json");
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