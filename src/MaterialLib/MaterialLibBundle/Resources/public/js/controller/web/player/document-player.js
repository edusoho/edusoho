define(function(require, exports, module) {

    var SlidePlayer = require('../../../../../topxiaweb/js/controller/widget/document-player');

    exports.run = function() {
    	var player = $("#document-player");

		$.get(player.data('url'), function(response) {
            var player = new DocumentPlayer({
                element: '#document-content',
                swfFileUrl: response.swfUri,
                pdfFileUrl: response.pdfUri
            });
        }, 'json');
	}
});