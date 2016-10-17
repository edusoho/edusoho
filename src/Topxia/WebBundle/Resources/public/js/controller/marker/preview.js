define(function (require, exports, module) {

    var Messenger = require('../player/messenger');
    var videoHtml = $('#lesson-dashboard');
    var courseId = videoHtml.data("course-id");
    var lessonId = videoHtml.data("lesson-id");
    var playerUrl = '/course/' + courseId + '/lesson/' + lessonId + '/player';

    var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
    $("#lesson-video-content").html(html);

});
