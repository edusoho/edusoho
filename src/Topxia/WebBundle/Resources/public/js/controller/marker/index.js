define(function(require, exports, module) {

    exports.run = function() {

        var playerUrl = '/course/1/lesson/9/player';
        var html = '<iframe src=\''+playerUrl+'\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
        $("#lesson-video-content").html(html);

        
    }
});