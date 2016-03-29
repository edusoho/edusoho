define(function(require, exports, module) {
	exports.run = function() {
		var lesson = app.arguments.lesson;

		if (lesson.mediaSource == 'self') {
            var lessonVideoDiv = $('#open-course-views');

            if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
                Notify.warning('视频文件正在转换中，稍后完成后即可查看');
                return;
            }

            var playerUrl = '../../course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
            if (self.get('starttime')) {
                playerUrl += "?starttime=" + self.get('starttime');
            }
            var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';

            $("#lesson-video-content").show();
            $("#lesson-video-content").html(html);

            var messenger = new Messenger({
                name: 'parent',
                project: 'PlayerProject',
                children: [document.getElementById('viewerIframe')],
                type: 'parent'
            });

            messenger.on("ready", function() {
                var player = window.frames["viewerIframe"].window.BalloonPlayer;
                var markersUrl = '/course/lesson/' + lesson.id + '/marker/show';
                $.ajax({
                    type: "get",
                    url: markersUrl,
                    dataType: "json",
                    success: function(data) {
                        var markers = new Array();

                        for (var index in data) {
                            var marker = {
                                "id": data[index].id,
                                "time": (parseInt(data[index].second) + data[index].videoHeaderTime),
                                "text": "ads",
                                "finished": data[index].finish
                            };
                            markers.push(marker);
                        }
                        if (data.length != 0) {
                            player.setMarkers(markers);
                        }
                    }
                });
            });

            messenger.on("ended", function() {
                var player = that.get("player");
                player.playing = false;
                that.set("player", player);
                that._onFinishLearnLesson();
            });

            messenger.on("playing", function() {
                var player = that.get("player");
                player.playing = true;
                that.set("player", player);
            });

            messenger.on("paused", function() {
                var player = that.get("player");
                player.playing = false;
                that.set("player", player);
            });

            messenger.on("onMarkerReached", function(marker,questionId){
                var player = window.frames["viewerIframe"].window.BalloonPlayer;
                if (player.isPlaying()) {
                    player.pause();
                }
                $.get('/course/lesson/' + marker.markerId + '/questionmarker/show', {
                    "questionId": marker.questionId,
                    "lessonId":lesson.id
                }, function(data) {
                    // $('.vjs-break-overlay-text').html(data);
                    var $modal = $("#modal");
                    if (data == "") {
                        $modal.hide();
                        player.finishMarker(marker.markerId, true);
                    } else {
                        $modal.html(data);
                        var $player = $(document.getElementById('viewerIframe').contentDocument);
                        //判断是否全屏
                        if ($player.width() == $('body').width()) {
                            $modal.css('z-index', '2147483647');
                        } else {
                            var $modaldialog = $modal.find('.modal-dialog');
                            $modaldialog.css('margin-left', ($('body').width() - $('.toolbar').width() - $modaldialog.width()) / 2);
                        }
                        $modal.show();
                    }
                });
            });
            that.set("player", {});
        } else {
            $("#lesson-swf-content").html('<div id="lesson-swf-player"></div>');
            swfobject.embedSWF(lesson.mediaUri,
                'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
                    wmode: 'opaque',
                    allowFullScreen: 'true'
                });
            $("#lesson-swf-content").show();
        }
	};
});