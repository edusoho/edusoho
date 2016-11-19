import Emitter from 'es6-event-emitter';
class AudioPlayer extends Emitter {
    constructor(options) {
        super();
        this.options = options;
        this.setup();
    }

    setup() {
        var that = this;
        var audioPlayer = {}; /*new  MediaElementPlayer(this.options.element, {
            mode: 'auto_plugin',
            enablePluginDebug: false,
            enableAutosize: true,
            success: function (media) {
                media.addEventListener("pause", function (e) {
                    that.trigger("paused", e);
                });
                media.addEventListener("play", function (e) {
                    that.trigger("playing", e);
                });
                media.addEventListener('loadedmetadata', function (e) {
                    that.trigger("ready", e);
                });
                media.addEventListener("ended", function (e) {
                    that.trigger("ended", e);
                });

                media.play();
            }
        });*/
        this.player = audioPlayer;
    }

    _setPlayer() {
        this.player = player;
    }

    _getPlayer() {
        return this.player;
    }

    play() {
        if (this._getPlayer().paused) {
            this._getPlayer().play();
        }
    }

    setCurrentTime() {

    }

    pause(e) {
        var player = this._getPlayer();
        player.pause();
    }

}

export default AudioPlayer;