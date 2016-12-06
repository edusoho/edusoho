/**
 * Created by Simon on 08/11/2016.
 */
import  swfobject from 'es-swfobject';
import  EsMessenger from '../../../common/messenger';
import  ActivityEmitter from '../../activity/activity-emitter';
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
    var messenger = new EsMessenger({
      name: 'parent',
      project: 'PlayerProject',
      children: [],
      type: 'parent'
    });

    messenger.on("ended", (msg) => {
      this.player.playing = false;
      this._onFinishLearnTask(msg);
    });

    messenger.on("playing", (msg)=> {
      this.player.playing = true;
    });

    messenger.on("paused", (msg)=> {
      this.player.playing = false;
    });

    messenger.on("timechange", (msg)=> {
      this.player.currentTime = msg.currentTime;
    })
  }

  _onFinishLearnTask(msg) {
    let emitter = new ActivityEmitter();
    emitter.emit('finish', {data: msg}).then(() => {
      console.log('vidoe.finish');
    }).catch((error) => {
      console.error(error);
    });
  }


}
let videoplay = new VideoPlay();
videoplay.play();
