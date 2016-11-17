import  PlayerFactory from './player-factory';
import  Messenger from 'es-messenger';
class Show {

    constructor(element) {
        let container = $(element);
        this.userId = container.data("userId");
        this.userName = container.data("userName");
        this.fileId = container.data("fileId");
        this.fileGlobalId = container.data("fileGlobalId");

        this.courseId = container.data("courseId");
        this.lessonId = container.data("lessonId");
        this.timelimit = container.data('timelimit');

        this.playerType = container.data('player');
        this.fileType = container.data('fileType');
        this.url = container.data('url');
        this.videoHeaderLength = container.data('videoHeaderLength');
        this.enablePlaybackRates = container.data('enablePlaybackRates');
        this.watermark = container.data('watermark');
        this.accesskey = container.data('accessKey');
        this.fingerprint = container.data('fingerprint');
        this.fingerprintSrc = container.data('fingerprintSrc');
        this.fingerprintTime = container.data('fingerprintTime');
        this.balloonVideoPlayer = container.data('balloonVideoPlayer');
        this.markerUrl = container.data('markerurl');
        this.starttime = container.data('starttime');
        this.agentInWhiteList = container.data('agentInWhiteList');
        this.disableVolumeButton = container.data('disableVolumeButton');
        this.disablePlaybackButton = container.data('disablePlaybackButton');
        this.disableResolutionSwitcher = container.data('disableResolutionSwitcher');
        this.html = "";
    }

    initView() {
        let view, html;

        if (this.fileType == 'video') {
            if (this.playerType == 'local-video-player') {
                html += '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>';
            } else {
                html += '<div id="lesson-player" style="width: 100%;height: 100%;"></div>';
            }
        } else if (this.fileType == 'audio') {
            view.parent().css({"margin-top": "-25px", "top": "50%"});
            html += '<audio id="lesson-player" width="90%" height="50">';
            html += '<source src="' + url + '" type="audio/mp3" />';
            html += '</audio>';
        }
        view.html(html);
        view.show();
    }

    initPlayer() {
        let playerFactory = new PlayerFactory();
        return playerFactory.create(
            this.playerType,
            {
                element: '#lesson-player',
                url: this.url,
                fingerprint: this.fingerprint,
                fingerprintSrc: this.fingerprintSrc,
                fingerprintTime: this.fingerprintTime,
                watermark: this.watermark,
                starttime: this.starttime,
                agentInWhiteList: this.agentInWhiteList,
                timelimit: this.timelimit,
                enablePlaybackRates: this.enablePlaybackRates,
                controlBar: {
                    disableVolumeButton: this.disableVolumeButton,
                    disablePlaybackButton: this.disablePlaybackButton,
                    disableResolutionSwitcher: this.disableResolutionSwitcher
                },
                statsInfo: {
                    accesskey: this.accesskey,
                    globalId: this.fileGlobalId,
                    userId: this.userId,
                    userName: this.userName
                },
                videoHeaderLength: this.videoHeaderLength
            }
        );
    }

    initMesseger() {
        return new Messenger({
            name: 'parent',
            project: 'PlayerProject',
            type: 'child'
        });
    }

    initEvent() {
        let player = this.initPlayer();
        let messenger = this.initMesseger();
        player.on("ready", function () {
            messenger.sendToParent("ready", {pause: true});
            if (playerType == 'local-video-player') {
                var time = DurationStorage.get(userId, fileId);
                if (time > 0) {
                    player.setCurrentTime(DurationStorage.get(userId, fileId));
                }
                player.play();
            } else if (playerType == 'balloon-cloud-video-player') {
                if (markerUrl) {
                    $.getJSON(markerUrl, function (questions) {
                        player.setQuestions(questions);
                    });
                }
            }
        });

        player.on('answered', function (data) {
            // @todo delete lessonId
            var finishUrl = '/course/lesson/marker/' + data.markerId + '/question_marker/' + data.id + '/finish';
            $.post(finishUrl, {
                "answer": data.answer,
                "type": data.type,
                "lessonId": lessonId
            }, function (result) {

            }, 'json');

        });

        player.on("timechange", function (data) {
            messenger.sendToParent("timechange", {pause: true, currentTime: data.currentTime});
            if (playerType == 'local-video-player') {
                if (parseInt(player.getCurrentTime()) != parseInt(player.getDuration())) {
                    DurationStorage.set(userId, fileId, player.getCurrentTime());
                }
            }
        });

        player.on("paused", function () {
            console.log('paused')
            messenger.sendToParent("paused", {pause: true});
        });

        player.on("playing", function () {
            console.log('playing')
            messenger.sendToParent("playing", {pause: false});
        });

        player.on("ended", function () {
            console.log('ended')
            messenger.sendToParent("ended", {stop: true});
            if (playerType == 'local-video-player') {
                DurationStorage.del(userId, fileId);
            }
        });
    }

}

new Show('#lesson-video-content');