define(function (require, exports, module) {
    var swfobject = require('swfobject'),
        Notify = require('common/bootstrap-notify');
    var _ = require('underscore');
    var Widget = require('widget');

    var Messenger = require('../player/messenger');
    var Backbone = require('backbone');
    require('../../util/qrcode').run();
    var Course = Backbone.Model.extend({
        idAttribute: 'id'
    });

    var Courses = Backbone.Collection.extend({
        model: Course
    });

    var isWxAndroidBrowser = function () {
        var ua = navigator.userAgent.toLowerCase();
        return /android/.test(ua) && /micromessenger/i.test(ua);
    };

    var ADModalView = Backbone.View.extend({
        template: _.template(require('./ad-modal-body.html')),
        initialize: function () {
            this.$el = $('#open-course-ad-modal');
            this.listenTo(this.collection, 'add change remove reset', this.render);
            this.collection.fetch();
        },

        render: function () {
            var self = this;
            var courseViews = this.collection.map(function (course) {
                return self.template(course.toJSON());
            });
            var html = _.reduce(courseViews, function (html, courseView) {
                return html + courseView;
            }, '');

            this.$el.find('.modal-body').html(html);
        },
        show: function () {
            if(this.$el.parent().find('.open-course-wechat-qrcode').length > 0 ){
                return;
            }
            if (isWxAndroidBrowser()) {
                document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = "none";
                this.$el.on('hide.bs.modal', function () {
                    document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = "block";
                });
            }

            this.$el.modal({
                backdrop: false
            });
        },

        hide: function () {
            this.$el.modal('hide');
        }
    });

    var OpenCoursePlayer = Widget.extend({
        attrs: {
            "url": null,
            "lesson": null,
            "getRecommendCourseUrl": null
        },
        events: {
            'click .js-player-replay': '_replay',
            'click .live-video-replay-btn': 'onLiveVideoPlay'
        },
        setup: function () {
            $('.media-unconvert').hide();
            $('.lesson-content').hide();
            this._showPlayer();
        },

        _showPlayer: function () {
            var url = this.get('url');
            var self = this;
            $.get(url, function (lesson) {

                if (lesson.mediaError) {
                    //Notify.danger(lesson.mediaError);
                    $('#media-error-dialog').show();
                    $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
                    return;
                }
                $('#media-error-dialog').hide();
                self.set('lesson', lesson);
                var mediaSourceActionsMap = {
                    'iframe': self._onIframe,
                    'self': self._onVideo
                };

                var caller = mediaSourceActionsMap[lesson.mediaSource];
                if (caller === undefined && (lesson.type == 'video' || lesson.type == 'audio')) {
                    caller = self._onSWF;
                }

                if (caller === undefined) {
                    return;
                }
                caller = _.bind(caller, self);
                caller();
            })
        },

        _onIframe: function () {
            var lesson = this.get('lesson');
            var $ifrimeContent = $('#lesson-preview-iframe');
            $ifrimeContent.empty();
            var html = '<iframe class="embed-responsive-item" src="' + lesson.mediaUri + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
            $ifrimeContent.html(html);
            $ifrimeContent.show();
        },

        _onVideo: function () {
            var lesson = this.get('lesson');
            
            if (lesson.type == 'video' || lesson.type == 'audio') {
                if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
                    $('.media-unconvert').show();
                    return;
                }
                var playerUrl = '/open/course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
            } else {
                return;
            }
            
            this._videoPlay(playerUrl);
        },

        _onSWF: function () {
            var lesson = this.get('lesson');
            var $swfContent = $('#lesson-preview-swf-player');
            swfobject.removeSWF('lesson-preview-swf-player');
            $swfContent.html('<div id="lesson-swf-player"></div>');
            swfobject.embedSWF(lesson.mediaUri,
                'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
                    wmode: 'opaque',
                    allowFullScreen: 'true'
                });
            $swfContent.show();
        },

        _onPlayEnd: function () {
            this._showADModal();
        },

        _showADModal: function () {
            if (this.get('adView') !== undefined) {
                this.get('adView').show();
                return;
            }

            var courses = new Courses();
            courses.url = this.get('getRecommendCourseUrl');

            var adView = new ADModalView({
                collection: courses
            });
            adView.show();
            this.set('adView', adView);
        },

        _replay: function () {
            var player = this.get('player');
            if (player === undefined) {
                window.location.reload();
            } else {
                player.replay();
                this.get('adView').hide();
            }
        },

        _getPlayer: function () {
            return window.frames["viewerIframe"].window.BalloonPlayer ||
                window.frames["viewerIframe"].window.player;
        },

        _videoPlay: function(playerUrl) {
            var $videoContent = $('#lesson-preview-player');
            $videoContent.html("");

            var html = '<iframe class="embed-responsive-item" src="' + playerUrl + '" name="viewerIframe" id="viewerIframe" width="100%" allowfullscreen webkitallowfullscreen height="100%"" style="border:0px;position:absolute; left:0; top:0;"></iframe>';

            $videoContent.show();
            $videoContent.html(html);

            var messenger = new Messenger({
                name: 'parent',
                project: 'PlayerProject',
                children: [document.getElementById('viewerIframe')],
                type: 'parent'
            });
            var self = this;

            messenger.on("ready", function () {
                var player = self._getPlayer();
                self.set('player', player);
            });

            messenger.on("ended", function () {
                var onPlayEnd = _.bind(self._onPlayEnd, self);
                onPlayEnd();
            });
        },

        onLiveVideoPlay: function(){
            $('.live-header-mask').hide();

            var self = this;
            $.get(this.get('url'), function (lesson) {

                if (lesson.mediaError) {
                    $('#media-error-dialog').show();
                    $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
                    return;
                }
                $('#media-error-dialog').hide();
                self.set('lesson', lesson);
            
                if (lesson.type == 'liveOpen' && lesson.replayStatus == 'videoGenerated') {
                    if ((lesson.mediaConvertStatus == 'waiting') || (lesson.mediaConvertStatus == 'doing')) {
                        $('.media-unconvert').show();
                        return;
                    }
                    var playerUrl = '/open/course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
                } else {
                    return;
                }

                self._videoPlay(playerUrl);
            })
        }
    });

    exports.run = function () {

        if ($('#firstLesson').length > 0) {
            var firstLessonUrl = $('#firstLesson').data('url');
            (new OpenCoursePlayer({
                url: firstLessonUrl,
                element: '.open-course-views',
            })).render();
        }

        $("#alert-btn").on('click', function () {
            var $btn = $(this);

            if (typeof($btn.attr("data-toggle")) == "undefined") {
                $.post($btn.data('url'), function (response) {
                    if (response['result']) {
                        $('.member-num').html(response['number']);
                        $btn.hide();
                        $("#alerted-btn").show();
                    } else {
                        Notify.danger(response['message']);
                    }

                });
            }

        });
    };

});