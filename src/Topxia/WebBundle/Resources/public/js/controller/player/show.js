define(function(require, exports, module) {

	var PlayerFactory = require('./player-factory');
	var Store = require('store');
	var Class = require('class');
    var Messenger = require('./messenger');

	exports.run = function() {
        var videoHtml = $('#lesson-video-content');
        var userId = videoHtml.data("userId");
        var mediaId = videoHtml.data("mediaId");
        var courseId = videoHtml.data("courseId");
        var lessonId = videoHtml.data("lessonId");
        var watchLimit = videoHtml.data('watchLimit');
        var fileType = videoHtml.data('fileType');
        var url = videoHtml.data('url');

        var html = "";
        if(fileType == 'video'){
            html = '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>';
        } else if(fileType == 'audio'){
            html = '<audio id="lesson-player" width="500" height="50">';
            html += '<source src="' + url + '" type="audio/mp3" />';
            html += '</audio>';

        }

        videoHtml.html(html);
        videoHtml.show();


		var playerFactory = new PlayerFactory();
		var player = playerFactory.create(
			videoHtml.data('player'),
			{
				element: '#lesson-player',
				url: url
			}
		);

		player.on("timechange", function(e){
            if(parseInt(player.getCurrentTime()) != parseInt(player.getDuration())){
                DurationStorage.set(userId, mediaId, player.getCurrentTime());
            }
        });
	
		player.on("ready", function(){
        	player.setCurrentTime(DurationStorage.get(userId, mediaId));
			player.play();
		});

        var messenger = new Messenger({
            name: 'parent',
            project: 'PlayerProject',
            type: 'child'
        });

        player.on('ended',function(){
            messenger.sendToParent("ended", {"success":"true"});
        })
		
        messenger.sendToParent("inited", {});

        var counter = new Counter(player, courseId, lessonId, watchLimit);
        counter.setTimerId(setInterval(function(){
        	counter.execute()
        }, 1000));
	};

	var DurationStorage = {
        set: function(userId,mediaId,duration) {
            var durations = Store.get("durations");
            if(!durations || !(durations instanceof Array)){
                durations = new Array();
            }

            var value = userId+"-"+mediaId+":"+duration;
            if(durations.length>0 && durations.slice(durations.length-1,durations.length)[0].indexOf(userId+"-"+mediaId)>-1){
                durations.splice(durations.length-1, durations.length);
            }
            if(durations.length>=20){
                durations.shift();
            }
            durations.push(value);
            Store.set("durations", durations);
        },
        get: function(userId,mediaId) {
            var durationTmpArray = Store.get("durations");
            if(durationTmpArray){
                for(var i = 0; i<durationTmpArray.length; i++){
                    var index = durationTmpArray[i].indexOf(userId+"-"+mediaId);
                    if(index>-1){
                        var key = durationTmpArray[i];
                        return parseFloat(key.split(":")[1])-5;
                    }
                }
            }
            return 0;
        },
        del: function(userId,mediaId) {
            var key = userId+"-"+mediaId;
            var durationTmpArray = Store.get("durations");
            for(var i = 0; i<durationTmpArray.length; i++){
                var index = durationTmpArray[i].indexOf(userId+"-"+mediaId);
                if(index>-1){
                    durationTmpArray.splice(i,1);
                }
            }
            Store.set("durations", durationTmpArray);
        }
    };

    var Counter = Class.create({
        initialize: function(player, courseId, lessonId, watchLimit) {
            this.player = player;
            this.courseId = courseId;
            this.lessonId = lessonId;
            this.interval = 120;
            this.watched = false;
            this.watchLimit = watchLimit;
        },

        setTimerId: function(timerId) {
            this.timerId = timerId;
        },

        execute: function(){
            var posted = this.addMediaPlayingCounter();
            this.addLearningCounter(posted);
        },

        addLearningCounter: function(promptlyPost) {
            var learningCounter = Store.get("lesson_id_"+this.lessonId+"_learning_counter");
            if(!learningCounter){
                learningCounter = 0;
            }
            learningCounter++;

            if(promptlyPost || learningCounter >= this.interval){
                var url="../../../../course/"+this.lessonId+'/learn/time/'+learningCounter;
                $.get(url);
                learningCounter = 0;
            }

            Store.set("lesson_id_"+this.lessonId+"_learning_counter", learningCounter);
        }, 

        addMediaPlayingCounter: function() {
            var mediaPlayingCounter = Store.get("lesson_id_"+this.lessonId+"_playing_counter");
            if(!mediaPlayingCounter){
                mediaPlayingCounter = 0;
            }
            var playing = this.player.isPlaying();

            if(!this.player) {
            	return;
            }

            var posted = false;
            if(mediaPlayingCounter >= this.interval || (mediaPlayingCounter>0 && !playing)){
                var url="../../../../course/"+this.lessonId+'/watch/time/'+mediaPlayingCounter;
                var self = this;
                $.get(url, function(response) {
                    if (self.watchLimit && response.watchLimited) {
                        window.location.reload();
                    }
                }, 'json');
                posted = true;
                mediaPlayingCounter = 0;
            } else if(playing) {
                mediaPlayingCounter++;
            }

            if (this.watchLimit && !this.watched && mediaPlayingCounter >= 1) {
                this.watched = true;
                var url = '../../../../course/' + this.courseId + '/lesson/' + this.lessonId + '/watch_num';
                $.get(url, function(result) {
                    if (result.status == 'ok') {
                        Notify.success('您已观看' + result.num + '次，剩余' + (result.limit - result.num) + '次。');
                    } else if (result.status == 'error') {
                        window.location.reload();
                    }

                }, 'json');
            }

            Store.set("lesson_id_"+this.lessonId+"_playing_counter", mediaPlayingCounter);

            return posted;
        }
    });

});