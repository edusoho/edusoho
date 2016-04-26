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
            
	        cloud2UploadStatus.currentFileSize = file.size;
	        cloud2UploadStatus.blockIndex = Math.ceil(cloud2UploadStatus.currentFileSize/cloud2UploadStatus.blockSize);
	        cloud2UploadStatus.chunkIndex = Math.ceil(cloud2UploadStatus.currentFileSize/cloud2UploadStatus.chunkSize);
	        if(cloud2UploadStatus.blockSize>cloud2UploadStatus.currentFileSize){
	            file.uploaderWidget.uploader.option('server', self.get('uploadUrl')+'/mkblk/'+cloud2UploadStatus.currentFileSize);
	        }else{
	            file.uploaderWidget.uploader.option('server', self.get('uploadUrl')+'/mkblk/'+cloud2UploadStatus.blockSize);
	        }
	        self.set('cloud2UploadStatus',cloud2UploadStatus);

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
            headers.Authorization = "UpToken "+self.get('uploadToken');
        },

        _initCloud2UploadStatus: function(){
            return {
                ctxs: new Array(),
                currentFileSize: 0,
                blockSize: 4*1024*1024,
                chunkSize: 1024*1024,
                blockIndex:0,
                currentBlockIndex: 1,
                chunkIndex:0,
                currentChunkIndex: 0
            };
        },

        finishUpload: function(deferred) {
        	var self = this.file.uploaderWidget;
        	var cloud2UploadStatus = self.get('cloud2UploadStatus');
            var url = '/uploader/mkfile/'+cloud2UploadStatus.currentFileSize;
            var result = {};
            $.ajax({
                url: url,
                type:'POST',
                async: false,
                data:{
                    'uploadUrl': encodeURI(self.get('uploadUrl')),
                    'content': cloud2UploadStatus.ctxs.join(','),
                    'id': this.file.fileId
                },
                beforeSend: function(xhr){
                    xhr.setRequestHeader("Authorization", "UpToken " + self.get('uploadToken'));
                },
                success:function(data) {
                    result = eval('('+data+')');
                }
            });
            return $.extend({id: this.file.fileId}, result);
        },

        uploadAccept: function(object, ret){
        	var self = this.file.uploaderWidget;
        	var cloud2UploadStatus = self.get('cloud2UploadStatus');
            cloud2UploadStatus.currentChunkIndex++;
            if(cloud2UploadStatus.currentChunkIndex%4 == 0){
                cloud2UploadStatus.currentBlockIndex++;
                if(cloud2UploadStatus.currentBlockIndex < cloud2UploadStatus.blockIndex){
                    self.uploader.option('server', self.get('uploadUrl')+'/mkblk/'+cloud2UploadStatus.blockSize);
                } else if(cloud2UploadStatus.currentBlockIndex == cloud2UploadStatus.blockIndex){
                    self.uploader.option('server', self.get('uploadUrl')+'/mkblk/'+(cloud2UploadStatus.currentFileSize%cloud2UploadStatus.blockSize));
                }
            } else {
                var offsetSize = cloud2UploadStatus.currentChunkIndex%4*cloud2UploadStatus.chunkSize;
                self.uploader.option('server', self.get('uploadUrl')+'/bput/'+ret.ctx+'/'+offsetSize);
            }
            if(cloud2UploadStatus.currentChunkIndex%4 == 0 || cloud2UploadStatus.chunkIndex == cloud2UploadStatus.currentChunkIndex){
                cloud2UploadStatus.ctxs.push(ret.ctx);
            }
            self.set('cloud2UploadStatus', cloud2UploadStatus);
        }
    });

    module.exports = Cloud2Strategy;
});