define(function(require, exports, module) {

	var Class = require('class');

    var LocalStrategy = Class.extend({
    	initialize: function(file, response) {
            file.uploaderWidget.uploader.option('server', response.url);
            file.uploaderWidget.uploader.option('formData', response.postParams);
        },
        
        uploadBeforeSend: function(object, data, headers){
            $.each(data, function(i, n){
                if(i!='file') {
                    delete data[i];
                }
            })
        },

        finishUpload: function(deferred) {
            return {id: this.file.id};
        },

        uploadAccept: function(object, ret){
        }
    });

    module.exports = LocalStrategy;
});