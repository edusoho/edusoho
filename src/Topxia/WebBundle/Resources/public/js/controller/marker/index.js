define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {

        var videoHtml = $('#lesson-dashboard');
        var courseId = videoHtml.data("course-id");
        var lessonId = videoHtml.data("lesson-id");
        var mediaId = videoHtml.data("lesson-mediaid");

        var playerUrl = '/course/'+courseId+'/lesson/'+lessonId+'/player?hideBeginning=true&hideQuestion=1';
        var html = '<iframe src=\''+playerUrl+'\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
        $("#lesson-video-content").html(html);

        $.get($('.question').data('url'),function(response){
        	$('.question').html(response);
        })
    }
});