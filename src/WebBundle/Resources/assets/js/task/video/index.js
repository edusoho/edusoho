/**
 * Created by Simon on 08/11/2016.
 */
import  swfobject from 'es-swfobject';
import  EsMessager from '../../../common/messenger';
class VideoPlay {
    constructor() {
        this.player = {};
    }

    play() {
        if ($('#swf-player').length) {
            this._playerSwf();
        } else {
            this._playVideo();
        }
    }

    _playerSwf() {
        const swf_dom = 'swf-player';
        swfobject.embedSWF($('#' + swf_dom).data('url'),
            swf_dom, '100%', '100%', "9.0.0", null, null, {
                wmode: 'opaque',
                allowFullScreen: 'true'
            });
    }

    _playVideo() {
        var messenger = new EsMessager({
            name: 'parent',
            project: 'PlayerProject',
            children: [],
            type: 'parent'
        });

        messenger.on("ended", (msg) => {
            this.player.playing = false;
            this._onFinishLearnLesson();
        });

        messenger.on("playing", (msg)=> {
            this.player.playing = true;
        });

        messenger.on("paused", (msg)=> {
            this.player.playing = false;
        });

        messenger.on("timechange", (msg)=> {
        })
    }

    _onFinishLearnLesson() {
        console.log(this.player);
        console.log('messenger------------', '_onFinishLearnLesson')
    }


}
let videoplay = new VideoPlay();
videoplay.play();

//
// _videoPlay: function (lesson) {
//     var self = this;
//
//     if (lesson.mediaSource == 'self') {
//         var lessonVideoDiv = $('#lesson-video-content');
//
//         if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
//             Notify.warning('视频文件正在转换中，稍后完成后即可查看');
//             return;
//         }
//
//         var playerUrl = '../../course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
//         if (self.get('starttime')) {
//             playerUrl += "?starttime=" + self.get('starttime');
//         }
//         var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
//
//         $("#lesson-video-content").show();
//         $("#lesson-video-content").html(html);
//
//         var messenger = new Messenger({
//             name: 'parent',
//             project: 'PlayerProject',
//             children: [document.getElementById('viewerIframe')],
//             type: 'parent'
//         });
//
//         messenger.on("ended", function () {
//             var player = self.get("player");
//             player.playing = false;
//             self.set("player", player);
//             self._onFinishLearnLesson();
//         });
//
//         messenger.on("playing", function () {
//             var player = self.get("player");
//             player.playing = true;
//             self.set("player", player);
//         });
//
//         messenger.on("paused", function () {
//             var player = self.get("player");
//             player.playing = false;
//             self.set("player", player);
//         });
//
//         self.set("player", {});
//     } else {
//         $("#lesson-swf-content").html('<div id="lesson-swf-player"></div>');
//         swfobject.embedSWF(lesson.mediaUri,
//             'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
//                 wmode: 'opaque',
//                 allowFullScreen: 'true'
//             });
//         $("#lesson-swf-content").show();
//     }
// }
// ,