define(function(require, exports, module) {

    var Store = require('store');
    var Class = require('class');
    var Messenger = require('./messenger');

    exports.run = function() {

        var videoHtml = $('#lesson-video-content');

        var userId = videoHtml.data("userId");
        var fileId = videoHtml.data("fileId");

        var courseId = videoHtml.data("courseId");
        var lessonId = videoHtml.data("lessonId");
        var timelimit = videoHtml.data('timelimit');

        var playerType = videoHtml.data('player');
        var fileType = videoHtml.data('fileType');
        var url = videoHtml.data('url');
        var videoHeaderLength = videoHtml.data('videoHeaderLength');
        var enablePlaybackRates = videoHtml.data('enablePlaybackRates');
        var watermark = videoHtml.data('watermark');
        var fingerprint = videoHtml.data('fingerprint');
        var fingerprintSrc = videoHtml.data('fingerprintSrc');
        var fingerprintTime = videoHtml.data('fingerprintTime');
        var balloonVideoPlayer = videoHtml.data('balloonVideoPlayer');
        var markerUrl = videoHtml.data('markerurl');
        var starttime = videoHtml.data('starttime');
        var agentInWhiteList = videoHtml.data('agentInWhiteList');
        var disableVolumeButton = videoHtml.data('disableVolumeButton');
        var disablePlaybackButton = videoHtml.data('disablePlaybackButton');
        var disableResolutionSwitcher = videoHtml.data('disableResolutionSwitcher');
        var html = "";
        if(fileType == 'video'){
            if (playerType == 'local-video-player'){
                html += '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>';
            } else {
                html += '<div id="lesson-player" style="width: 100%;height: 100%;"></div>';
            }
        }else if(fileType == 'audio'){
            videoHtml.parent().css({"margin-top":"-25px","top":"50%"});
            html += '<audio id="lesson-player" width="90%" height="50">';
            html += '<source src="' + url + '" type="audio/mp3" />';
            html += '</audio>';
        }

        videoHtml.html(html);
        videoHtml.show();

        var PlayerFactory = require('./player-factory');
        var playerFactory = new PlayerFactory();
        var player = playerFactory.create(
            playerType,
            {
                element: '#lesson-player',
                url: url,
                fingerprint: fingerprint,
                fingerprintSrc: fingerprintSrc,
                fingerprintTime:fingerprintTime,
                watermark: watermark,
                starttime: starttime,
                agentInWhiteList: agentInWhiteList,
                timelimit: timelimit,
                enablePlaybackRates: enablePlaybackRates,
                controlBar: {
                    disableVolumeButton: disableVolumeButton,
                    disablePlaybackButton: disablePlaybackButton,
                    disableResolutionSwitcher: disableResolutionSwitcher
                },
                videoHeaderLength: videoHeaderLength
            }
        );

        var messenger = new Messenger({
            name: 'parent',
            project: 'PlayerProject',
            type: 'child'
        });

        //为了不把播放器对象暴露到其他js中，所以把设置操作message过来
        messenger.on('setPlayerPause', function() {
            player.pause();
        });

        messenger.on('setPlayerPlay', function() {
            player.play();
        });
        
        player.on("ready", function(){
            messenger.sendToParent("ready", {pause: true});
            if (playerType == 'local-video-player') {
                var time = DurationStorage.get(userId, fileId);
                if(time>0){
                    player.setCurrentTime(DurationStorage.get(userId, fileId));
                }
                player.play();
            } else if (playerType == 'balloon-cloud-video-player'){
                if (markerUrl) {
                    $.getJSON(markerUrl, function(questions) {
                        player.setQuestions(questions);
                    });
                }
            }
        });

        player.on('answered', function(data) {
            // @todo delete lessonId
            var finishUrl = '/course/lesson/marker/' + data.markerId + '/question_marker/' + data.id + '/finish';
            $.post(finishUrl, {
                "answer": data.answer,
                "type": data.type,
                "lessonId": lessonId
            }, function(result) {

            }, 'json');

        });

        player.on("timechange", function(data){
            messenger.sendToParent("timechange", {pause: true, currentTime: data.currentTime});
            if (playerType == 'local-video-player'){
                if(parseInt(player.getCurrentTime()) != parseInt(player.getDuration())){
                    DurationStorage.set(userId, fileId, player.getCurrentTime());
                }
            }
        });

        player.on("paused", function(){
            messenger.sendToParent("paused", {pause: true});
        });

        player.on("playing", function(){
            messenger.sendToParent("playing", {pause: false});
        });

        player.on("ended", function(){
            messenger.sendToParent("ended", {stop: true});
            if (playerType == 'local-video-player') {
                DurationStorage.del(userId, fileId);
            }
        });

    };

    var DurationStorage = {
        set: function(userId,fileId,duration) {
            var durations = Store.get("durations");
            if(!durations || !(durations instanceof Array)){
                durations = new Array();
            }

            var value = userId+"-"+fileId+":"+duration;
            if(durations.length>0 && durations.slice(durations.length-1,durations.length)[0].indexOf(userId+"-"+fileId)>-1){
                durations.splice(durations.length-1, durations.length);
            }
            if(durations.length>=20){
                durations.shift();
            }
            durations.push(value);
            Store.set("durations", durations);
        },
        get: function(userId,fileId) {
            var durationTmpArray = Store.get("durations");
            if(durationTmpArray){
                for(var i = 0; i<durationTmpArray.length; i++){
                    var index = durationTmpArray[i].indexOf(userId+"-"+fileId);
                    if(index>-1){
                        var key = durationTmpArray[i];
                        return parseFloat(key.split(":")[1])-5;
                    }
                }
            }
            return 0;
        },
        del: function(userId,fileId) {
            var key = userId+"-"+fileId;
            var durationTmpArray = Store.get("durations");
            for(var i = 0; i<durationTmpArray.length; i++){
                var index = durationTmpArray[i].indexOf(userId+"-"+fileId);
                if(index>-1){
                    durationTmpArray.splice(i,1);
                }
            }
            Store.set("durations", durationTmpArray);
        }
    };

});