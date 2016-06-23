define(function(require, exports, module) {

    var Messenger = require('./messenger');

    exports.run = function() {

        var videoHtml = $('#lesson-video-content');

        // var userId = videoHtml.data("userId");
        // var fileId = videoHtml.data("fileId");

        // var courseId = videoHtml.data("courseId");
        // var lessonId = videoHtml.data("lessonId");
        var timelimit = videoHtml.data('timelimit');

        // var playerType = videoHtml.data('player');
        // var fileType = videoHtml.data('fileType');
        var url = videoHtml.data('url');
        var watermark = videoHtml.data('watermark');
        var fingerprint = videoHtml.data('fingerprint');
        // var fingerprintSrc = videoHtml.data('fingerprintSrc');
        // var balloonVideoPlayer = videoHtml.data('balloonVideoPlayer');
        var markerUrl = videoHtml.data('markerurl');
        // var starttime = videoHtml.data('starttime');
        var agentInWhiteList = videoHtml.data('agentInWhiteList');

        // var PlayerFactory = require('./player-factory');
        // var playerFactory = new PlayerFactory();
        // var player = playerFactory.create(
        //     playerType,
        //     {
        //         element: '#lesson-player',
        //         url: url,
        //         fingerprint: fingerprint,
        //         fingerprintSrc: fingerprintSrc,
        //         watermark: watermark,
        //         starttime: starttime,
        //         agentInWhiteList: agentInWhiteList,
        //         timelimit:timelimit
        //     }
        // );
        var player = new VideoPlayerSDK({
            id: 'lesson-video-content',
            // disableControlBar: true,
            // disableProgressBar: true,
            playlist: url,
            fingerprint: {
                html: fingerprint,
                duration: 2000
            },
            watermark: {
                file: watermark,
                xpos: 0,
                ypos: 0,
                xrepeat: 0,
                opacity: 0.5,
            },
            pluck: {
                timelimit: timelimit,
                text: "免费试看结束，购买后可完整观看",
                display: true
            },
            remeberLastPos : true
        });

        var messenger = new Messenger({
            name: 'parent',
            project: 'PlayerProject',
            type: 'child'
        });

        player.on('timeupdate', function(data) {
            messenger.sendToParent("timechange", {pause: true});
            console.log(data.currentTime);
        });

        player.on('anwsered', function(data) {
            console.log('anwsered', data);
        });

        // player.on("timechange", function(e){

        // });

        // player.on("firstplay", function(){
 
        // });
        
        player.on("ready", function(){
            messenger.sendToParent("ready", {pause: true});
            player.play();
        });
        player.on("onMarkerReached",function(markerId,questionId){
            messenger.sendToParent("onMarkerReached", {pause: true,markerId:markerId,questionId:questionId});
        });

        player.on("timechange", function(){
            messenger.sendToParent("timechange", {pause: true});
        });

        player.on("paused", function(){
            messenger.sendToParent("paused", {pause: true});
        });

        player.on("playing", function(){
            messenger.sendToParent("playing", {pause: false});
        });

        player.on("ended", function(){
            messenger.sendToParent("ended", {stop: true});
        });

    };

});