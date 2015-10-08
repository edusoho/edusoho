define(function(require, exports, module) {

	
	var Store = require('store');
	var Class = require('class');
    var Messenger = require('./messenger');
    var swfobject = require('swfobject');

	exports.run = function() {


        var videoHtml = $('#lesson-video-content');

        var userId = videoHtml.data("userId");
        var fileId = videoHtml.data("fileId");

        var courseId = videoHtml.data("courseId");
        var lessonId = videoHtml.data("lessonId");
        var watchLimit = videoHtml.data('watchLimit');

        var fileType = videoHtml.data('fileType');
        var url = videoHtml.data('url');
        var watermark = videoHtml.data('watermark');
        var fingerprint = videoHtml.data('fingerprint');
        var fingerprintSrc = videoHtml.data('fingerprintSrc');
        var agentInWhiteList = videoHtml.data('agentInWhiteList');
        var balloonVideoPlayer = videoHtml.data('balloonVideoPlayer');

        var html = "";

        if(fileType == 'video'){
            if (videoHtml.data('player') == 'local-video-player'){
                html += '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>';
            } else {
                html += '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin"></video>';
            }
        }else if(fileType == 'audio'){
            html += '<audio id="lesson-player" width="500" height="50">';
            html += '<source src="' + url + '" type="audio/mp3" />';
            html += '</audio>';
        }

        if (balloonVideoPlayer && fileType == 'video' && !swfobject.hasFlashPlayerVersion('11') && !agentInWhiteList) {
            html = '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
            html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            html += '<span aria-hidden="true">×</span>';
            html += '</button>';
            html += '您的浏览器未安装Flash播放器或版本太低，请先安装Flash播放器，';
            html += '或前往<a href="/mobile" target="parent">下载App</a>。';
            html += '</div>';
            videoHtml.html(html);
            videoHtml.show();

            return;
        }

        videoHtml.html(html);
        videoHtml.show();

        var PlayerFactory = require('./player-factory');
        var playerFactory = new PlayerFactory();
        var player = playerFactory.create(
            videoHtml.data('player'),
            {
                element: '#lesson-player',
                url: url,
                fingerprint: fingerprint,
                fingerprintSrc: fingerprintSrc,
                watermark: watermark
            }
        );

        var messenger = new Messenger({
            name: 'parent',
            project: 'PlayerProject',
            type: 'child'
        });

        player.on("timechange", function(e){
            if(parseInt(player.getCurrentTime()) != parseInt(player.getDuration())){
                DurationStorage.set(userId, fileId, player.getCurrentTime());
            }
        });
    
        player.on("ready", function(){
            var time = DurationStorage.get(userId, fileId);
            if(time>0){
                player.setCurrentTime(DurationStorage.get(userId, fileId));
            }
            player.play();
        });

        player.on("paused", function(){
            messenger.sendToParent("paused", {pause: true});
        });

        player.on("playing", function(){
            messenger.sendToParent("playing", {pause: false});
        });

        player.on("ended", function(){
            messenger.sendToParent("ended", {stop: true});
            DurationStorage.del(userId, fileId);
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