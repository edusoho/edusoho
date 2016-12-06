import  EsMessenger from '../../../common/messenger';
class VideoPlay {
    constructor(elment) {
        this.dom = $(elment);
        this.data = this.dom.data();
        this.player = {};
    }

    play() {
        var messenger = new EsMessenger({
            name: 'EsMessenger',
            project: 'PlayerProject',
            children: [],
            type: 'parent'
        });

        messenger.on("ended", (msg)=> {
            this.player.playing = false;
            this._onFinishLearnTask();
        });

        messenger.on("playing", (msg) => {
            this.player.playing = true;
        });

        messenger.on("paused", (msg)=> {
            this.player.playing = false;
        });

        messenger.on("timechange", (msg)=> {
        })
    }

    _onFinishLearnTask() {
        console.log('messenger------------', '_onFinishLearnTask')
    }


}
let videoPlay = new VideoPlay("#audio-content");
videoPlay.play();
