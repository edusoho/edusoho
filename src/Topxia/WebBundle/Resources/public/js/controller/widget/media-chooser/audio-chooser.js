define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser');

    var VideoChooser = BaseChooser.extend({
    	attrs: {
    		uploaderSettings: {
                file_types : "*.mp3",
                file_size_limit : "100 MB",
                file_types_description: "音频文件"
    		}
    	}

    });

    module.exports = VideoChooser;

});


