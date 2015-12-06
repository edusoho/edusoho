define(function(require, exports, module) {

	var Class = require('class');

    var LocalStrategy = Class.extend({
    	initialize: function(file) {
        },
        
        uploadBeforeSend: function(object, data, headers){
        },

        finishUpload: function(deferred) {
            return {id: this.file.outerId};
        },

        uploadAccept: function(object, ret){
        }
    });

    module.exports = LocalStrategy;
});