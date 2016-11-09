/**
 * Created by Simon on 08/11/2016.
 */
/*
 _videoPlay: function(lesson){
 var self = this;

 if (lesson.mediaSource == 'self') {
 var lessonVideoDiv = $('#lesson-video-content');

 if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
 Notify.warning('视频文件正在转换中，稍后完成后即可查看');
 return;
 }

 var playerUrl = '../../course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
 if (self.get('starttime')) {
 playerUrl += "?starttime=" + self.get('starttime');
 }
 var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';

 $("#lesson-video-content").show();
 $("#lesson-video-content").html(html);

 var messenger = new Messenger({
 name: 'parent',
 project: 'PlayerProject',
 children: [document.getElementById('viewerIframe')],
 type: 'parent'
 });

 messenger.on("ended", function() {
 var player = self.get("player");
 player.playing = false;
 self.set("player", player);
 self._onFinishLearnLesson();
 });

 messenger.on("playing", function() {
 var player = self.get("player");
 player.playing = true;
 self.set("player", player);
 });

 messenger.on("paused", function() {
 var player = self.get("player");
 player.playing = false;
 self.set("player", player);
 });

 self.set("player", {});
 } else {
 $("#lesson-swf-content").html('<div id="lesson-swf-player"></div>');
 swfobject.embedSWF(lesson.mediaUri,
 'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
 wmode: 'opaque',
 allowFullScreen: 'true'
 });
 $("#lesson-swf-content").show();
 }
 },*/
import  swfobject from 'swfobject';
let activity = $('.dashboard-body').data('activity');
console.log(activity.ext)
$("#lesson-swf-content").html('<div id="lesson-swf-player"></div>');
swfobject.embedSWF(activity.ext.mediaUri,
    'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
        wmode: 'opaque',
        allowFullScreen: 'true'
    });
// swfobject.test.swf("http://player.youku.com/player.php/sid/XMTgwOTg4NDM4OA==/v.swf", "lesson-swf-player", '100%', '100%', "9.0.0", "expressInstall.swf",{
//     wmode: 'opaque',
//     allowFullScreen: 'true'
// });