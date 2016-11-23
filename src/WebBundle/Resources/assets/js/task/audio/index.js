import  EsMessager from '../../../common/messenger';
class VideoPlay {
    constructor(elment) {
        this.dom = $(elment);
        this.data = this.dom.data();
        this.player = {};
    }

    play() {
        var messenger = new EsMessager({
            name: 'parent',
            project: 'PlayerProject',
            children: [document.querySelector('iframe[id=task-content-iframe]')],
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
