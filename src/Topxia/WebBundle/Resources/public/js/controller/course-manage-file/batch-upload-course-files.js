define(function(require, exports, module) {

    var ChunkUpload = require('../widget/media-chooser/chunk-upload');

    exports.run = function() {
		var chunkUpload = new ChunkUpload({
	        element: '#selectFiles'
	    });
	}
});