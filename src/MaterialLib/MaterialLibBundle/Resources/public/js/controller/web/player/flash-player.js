define(function(require, exports, module) {

    var swfobject = require('swfobject');
    exports.run = function() {
        if (!swfobject.hasFlashPlayerVersion('11')) {
            var html = '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
            html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            html += '<span aria-hidden="true">×</span>';
            html += '</button>';
            html += '您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。';
            html += '</div>';
            $("#flash-player").html(html);
            $("#flash-player").show();
        } else {
            var flashPlayer = $("#flash-player");
            swfobject.embedSWF(flashPlayer.data('url'),
                'flash-player', '100%', '100%', "9.0.0", null, null, {
                    wmode: 'opaque',
                    Fullscreen: true,
                    allowFullScreen: 'true'
                });
            $("#flash-player").show();
        }
    }
});