define(function (require, exports, module) {
    var Messenger = require('../player/messenger');

    var videoHtml = $('#lesson-dashboard');
    var courseId = videoHtml.data("course-id");
    var lessonId = videoHtml.data("lesson-id");
    var mediaId = videoHtml.data("lesson-mediaid");
    var playerUrl = '/course/' + courseId + '/lesson/' + lessonId + '/player?hideBeginning=true&hideQuestion=1&hideSubtitle=1';
    var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
    $("#lesson-video-content").html(html);
    
    module.exports = new Messenger({
        name: 'parent',
        project: 'PlayerProject',
        children: [document.getElementById('viewerIframe')],
        type: 'parent'
    });

});
