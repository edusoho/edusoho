/**
 * Created by Simon on 10/11/2016.
 */
/**
 * Created by Simon on 08/11/2016.
 */
import  EsMessager from '../../../common/messenger';
class VideoPlay {
    constructor(elment) {
        this.dom = $(elment);
        this.data = this.dom.data();
        this.player = {};
    }

    play() {
        console.log(this.data.media);

        // if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
        //     Notify.warning('视频文件正在转换中，稍后完成后即可查看');
        //     return;
        // }
        let startTime = this.data.startTime | 0;
        let playerUrl = `/course/${this.data.courseId}/task/${this.data.taskId}/player`;// '../../course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
        if (startTime) {
            playerUrl += "?starttime=" + startTime;
        }
        const html = `<iframe src='${playerUrl}' name='viewerIframe' id='viewerIframe' width='100%' allowfullscreen webkitallowfullscreen height='100%' style='border:0px'></iframe>`;
        let self = this;
        this.dom.show();
        this.dom.html(html);

        var messenger = new EsMessager({
            name: 'parent',
            project: 'PlayerProject',
            children: [document.getElementById('viewerIframe')],
            type: 'parent'
        });

        messenger.on("ended", function () {
            console.log('messenger------------', 'ended')
            var player = self.player;
            player.playing = false;
            self.player = player;
            self._onFinishLearnLesson();
        });

        messenger.on("playing", function () {
            console.log('messenger------------', 'playing')
            var player = self.player;
            player.playing = true;
            self.player = player;
        });

        messenger.on("paused", function () {
            console.log('messenger------------', 'paused')
            var player = self.player;
            player.playing = false;
            self.player = player;
        });
    }

    _onFinishLearnLesson() {
        console.log('messenger------------', '_onFinishLearnLesson')
    }


}
let videoPlay = new VideoPlay("#audio-content");
videoPlay.play();
