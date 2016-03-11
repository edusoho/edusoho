define(function(require, exports, module) {

	var Class = require('class');

    var Cloud2Strategy = Class.extend({
    	initialize: function(file, response) {

            file.gid = response.globalId;
            file.globalId = response.globalId;
            file.fileId = response.outerId;

            this.file = file;

            var self = file.uploaderWidget;
            self.set('uploadUrl', response.uploadUrl);
            self.set('uploadProxyUrl', response.uploadProxyUrl);
            self.set('uploadMode', response.uploadMode);
            self.set('uploadId', response.uploadToken);
            if (self.get('initResponse') && self.get('initResponse')['uploadToken']) {
                self.set('uploadId', self.get('initResponse')['uploadToken']);
            }
            var baiduParts = { parts: new Array()};
            self.set('baiduParts',baiduParts);

            self.uploader.option('method', 'PUT');
            self.uploader.option('chunked', true);
            self.uploader.option('chunkSize', 1024*1024*5);
            self.uploader.option('chunkRetry', 2);
            self.uploader.option('sendAsBinary', true);
            self.uploader.option('threads', 5);

        },

        uploadBeforeSend: function(object, data, headers, tr){
            var self = this.file.uploaderWidget;            

            var partNumber = object.chunk + 1;
            var encryptParams = {
                "partNumber" : partNumber,
                "uploadId" : self.get('uploadId')
            }
            var authResult = this._getUploadAuth(encryptParams, 'PUT');
        	
            headers['x-bce-date'] = authResult['x-bce-date'];
            headers['Authorization'] = authResult['Authorization'];

            $.each(data, function(i, n){
                delete data[i];
            });

            tr.options.server = self.get('uploadUrl')+'?partNumber='+partNumber+'&uploadId='+self.get('uploadId');
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

        _getParameterByName: function (name, url) {
            var query = url.substring( url.indexOf('?') + 1 );
            var vars = query.split('&');
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) == name) {
                    return decodeURIComponent(pair[1]);
                }
            }
        },

        finishUpload: function(deferred) {
            var self = this.file.uploaderWidget;
            var baiduParts = self.get('baiduParts');
            baiduParts.parts = baiduParts.parts.sort(function (a, b) {
                return (a['partNumber'] - b['partNumber']);
            });
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
            if (ret._responseHeaders && ret._responseHeaders['ETag']) {
                var partNumber = this._getParameterByName('partNumber', ret._requestURL);
                var baiduParts = self.get('baiduParts');
                baiduParts.parts.push({partNumber:parseInt(partNumber), eTag : ret._responseHeaders['ETag'].replace(/\"/g, '')}); 
                self.set('baiduParts', baiduParts);
            }
        }
    });

    module.exports = Cloud2Strategy;
});