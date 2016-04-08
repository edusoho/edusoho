define(function(require, exports, module) {
	var VideoJS = require('video-js'),
		swfobject = require('swfobject');


	var SlidePlayer = require('topxiawebbundle/controller/widget/slider-player');
    var DocumentPlayer = require('topxiawebbundle/controller/widget/document-player');
    var Messenger = require('topxiawebbundle/controller/player/messenger');

    exports.run = function() {

        var playerDiv = $('#material-preview-player');
        var url = playerDiv.data("url");
        var fileType = playerDiv.data("fileType");

        if ($("#material-preview-player").length > 0) {
            if($.inArray(fileType, ['video', 'audio', 'ppt', 'document'])>=0) {
                var html = '<iframe src=\''+url+'\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
                playerDiv.html(html);
            } else if (fileType=='image') {
    			$.post(url,function(response){
                    var html = '<img src = "'+response.url+'">';
                    playerDiv.html(html);
                });
            } else if (fileType=='flash') {
                if (!swfobject.hasFlashPlayerVersion('11')) {
                    var html = '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
                    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    html += '<span aria-hidden="true">×</span>';
                    html += '</button>';
                    html += '您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。';
                    html += '</div>';
                    player.html(html);
                } else {
                    $.get(url, function(response) {
                        var html = '<div id="swf-player"></div>';
                        playerDiv.html(html);
                        swfobject.embedSWF(response.mediaUri,
                            'swf-player', '100%', '100%', "9.0.0", null, null,
                            {wmode:'opaque',allowFullScreen:'true'});
                    });
                }
                player.css("height", '360px');
            }
        }

		var $modal = $('#modal');
        $modal.on('hidden.bs.modal', function(){
            if ($("#material-preview-player").length > 0) {
                $("#material-preview-player").html("");
            }
        });
        
    };

});
