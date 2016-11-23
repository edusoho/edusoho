/**
 * Created by Simon on 08/11/2016.
 */
import  swfobject from 'es-swfobject';
import  EsMessager from '../../player/util/messenger';
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
            this._onFinishLearnTask();
        });

        messenger.on("playing", (msg)=> {
            this.player.playing = true;
        });

        messenger.on("paused", (msg)=> {
            this.player.playing = false;
        });

        messenger.on("timechange", (msg)=> {
            console.log('timechange', msg)
        })
    }

    _onFinishLearnTask() {
        console.log(this.player);
        console.log('messenger------------', '_onFinishLearnTask')
    }


}
let videoplay = new VideoPlay();
videoplay.play();
