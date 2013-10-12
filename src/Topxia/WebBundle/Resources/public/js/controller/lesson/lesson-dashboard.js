define(function(require, exports, module) {

	var Widget = require('widget'),
		Backbone = require('backbone'),
        VideoJS = require('video-js'),
        swfobject = require('swfobject'),
        Scrollbar = require('jquery.perfect-scrollbar'),
        Notify = require('common/bootstrap-notify');

	var Toolbar = require('./lesson-toolbar');

	var Dashboard = Widget.extend({

		_router: null,

		_toolbar: null,

		_lessons: [],

		events: {
			'click [data-role=next-lesson]': 'onNextLesson',
			'click [data-role=prev-lesson]': 'onPrevLesson',
			'click [data-role=finish-lesson]': 'onFinishLesson'
		},

		attrs: {
			courseId: null,
			courseUri: null,
			dashboardUri: null,
			lessonId: null
		},

		setup: function() {
			this._readAttrsFromData();
			this._initToolbar();
			this._initRouter();
			this._initListeners();
		},

		onNextLesson: function(e) {
			var next = this._getNextLessonId();
			if (next > 0) {
				this._router.navigate('lesson/' + next, {trigger: true});
			}
		},

		onPrevLesson: function(e) {
			var prev = this._getPrevLessonId();
			if (prev > 0) {
				this._router.navigate('lesson/' + prev, {trigger: true});
			}
		},

		onFinishLesson: function(e) {
			var $btn = this.element.find('[data-role=finish-lesson]');
			if ($btn.hasClass('btn-success')) {
				this._onCancelLearnLesson();
			} else {
				this._onFinishLearnLesson();
			}
		},

		_startLesson: function() {
			var toolbar = this._toolbar,
				self = this;
			var url = '../../course/' + this.get('courseId') + '/lesson/' + this.get('lessonId') + '/learn/start';
			$.post(url, function(result) {
				if (result == true) {
					toolbar.trigger('learnStatusChange', {lessonId:self.get('lessonId'), status: 'learning'});
				}
			}, 'json');
		},

		_onFinishLearnLesson: function() {
			var $btn = this.element.find('[data-role=finish-lesson]'),
				toolbar = this._toolbar,
				self = this;
			var url = '../../course/' + this.get('courseId') + '/lesson/' + this.get('lessonId') + '/learn/finish';
			$.post(url, function(json) {
				$btn.addClass('btn-success');
				toolbar.trigger('learnStatusChange', {lessonId:self.get('lessonId'), status: 'finished'});
			}, 'json');
		},

		_onCancelLearnLesson: function() {
			var $btn = this.element.find('[data-role=finish-lesson]'),
				toolbar = this._toolbar,
				self = this;
			var url = '../../course/' + this.get('courseId') + '/lesson/' + this.get('lessonId') + '/learn/cancel';
			$.post(url, function(json) {
				$btn.removeClass('btn-success');
				toolbar.trigger('learnStatusChange', {lessonId:self.get('lessonId'), status: 'learning'});
			}, 'json');
		},

		_readAttrsFromData: function() {
			this.set('courseId', this.element.data('courseId'));
			this.set('courseUri', this.element.data('courseUri'));
			this.set('dashboardUri', this.element.data('dashboardUri'));
		},

		_initToolbar: function() {
	        this._toolbar = new Toolbar({
	            element: '#lesson-dashboard-toolbar',
	            activePlugins: ['lesson', 'question', 'note', 'material', 'quiz'],
	            courseId: this.get('courseId')
	        }).render();
		},

		_initRouter: function() {
			var that = this,
				DashboardRouter = Backbone.Router.extend({
	            routes: {
	                "lesson/:id": "lessonShow"
	            },

	            lessonShow: function(id) {
	                that.set('lessonId', id);
	            }
	        });

	        this._router = new DashboardRouter();
	        Backbone.history.start({pushState: false, root:this.get('dashboardUri')} );
		},

		_initListeners: function() {
			var that = this;
			this._toolbar.on('lessons_ready', function(lessons){
				that._lessons = lessons;
				that._showOrHideNavBtn();
			});
		},

		_onChangeLessonId: function(id) {
            if (!this._toolbar) {
            	return ;
            }
            this._toolbar.set('lessonId', id);

            if (VideoJS.players["lesson-video-player"]) {
            	VideoJS.players["lesson-video-player"].dispose();
            	$("#lesson-video-content").html('<video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"></video>');
            }

            var player = VideoJS("lesson-video-player", {
            	techOrder: ['flash','html5']
            });
            swfobject.removeSWF('lesson-swf-player');

            this.element.find('[data-role=lesson-content]').hide();

			var that = this;
            $.get(this.get('courseUri') + '/lesson/' + id, function(lesson){
            	that.element.find('[data-role=lesson-title]').html(lesson.title);
            	that.element.find('[data-role=lesson-number]').html(lesson.number);
            	if (parseInt(lesson.chapterNumber) > 0) {
	            	that.element.find('[data-role=chapter-number]').html(lesson.chapterNumber).parent().show();
            	} else {
            		that.element.find('[data-role=chapter-number]').parent().hide();
            	}

            	if ( (lesson.status != 'published') && !/preview=1/.test(window.location.href)) {
            		$("#lesson-unpublished-content").show();
            		return;
            	}

            	if (lesson.type == 'video') {
            		if (lesson.media.source == 'self') {
			            player.dimensions('100%', '100%');
			            player.src(lesson.media.files[0].url);
			            player.on('ended', function(){
			            	that._onFinishLearnLesson();
			            	player.currentTime(0);
			            	player.pause();
			            });
			       
			            player.on('error', function(error){
			            	var message = '您的浏览器不能播放当前视频，请<a href="' + 'http://get.adobe.com/flashplayer/' + '" target="_blank">点击此处安装Flash播放器</a>。';
			            	Notify.danger(message, 60);
			            });
			            $("#lesson-video-content").show();
			            player.play();
            		} else {
            			$("#lesson-swf-content").html('<div id="lesson-swf-player"></div>');
            			swfobject.embedSWF(lesson.media.files[0].url, 
            				'lesson-swf-player', '100%', '100%', "9.0.0", null, null, 
            				{wmode:'opaque',allowFullScreen:'true'});
            			$("#lesson-swf-content").show();
            		}
            	} else if (lesson.type == 'audio') {
            		player.dimensions('100%', '100%');
            		player.src(lesson.media.files[0].url);
		            player.on('ended', function(){
		            	that._onFinishLearnLesson();
		            });
            		$("#lesson-video-content").show();
            		player.play();
            	} else if (lesson.type == 'text') {
            		$("#lesson-text-content").find('.lesson-content-text-body').html(lesson.content);
            		$("#lesson-text-content").show();
            		$("#lesson-text-content").perfectScrollbar({wheelSpeed:50});
					$("#lesson-text-content").scrollTop(0);
					$("#lesson-text-content").perfectScrollbar('update');
            	}
            	that._toolbar.set('lesson', lesson);
            	that._startLesson();
            }, 'json');

            $.get(this.get('courseUri') + '/lesson/' + id + '/learn/status', function(json) {
            	var $finishButton = that.element.find('[data-role=finish-lesson]');
            	if (json.status != 'finished') {
	            	$finishButton.removeClass('btn-success');
            	} else {
            		$finishButton.addClass('btn-success');
            	}
            }, 'json');

            this._showOrHideNavBtn();

		},

		_showOrHideNavBtn: function() {
			var $prevBtn = this.$('[data-role=prev-lesson]'),
				$nextBtn = this.$('[data-role=next-lesson]'),
				index = $.inArray(parseInt(this.get('lessonId')), this._lessons);
			$prevBtn.show();
			$nextBtn.show();

			if (index < 0) {
				return ;
			}

			if (index === 0) {
				$prevBtn.hide();
			} else if (index === (this._lessons.length - 1)) {
				$nextBtn.hide();
			}

		},

		_getNextLessonId: function(e) {

			var index = $.inArray(parseInt(this.get('lessonId')), this._lessons);
			if (index < 0) {
				return -1;
			}

			if (index + 1 >= this._lessons.length) {
				return -1;
			}

			return this._lessons[index+1];
		},

		_getPrevLessonId: function(e) {
			var index = $.inArray(parseInt(this.get('lessonId')), this._lessons);
			if (index < 0) {
				return -1;
			}

			if (index == 0 ) {
				return -1;
			}

			return this._lessons[index-1];
		}

	});

	module.exports = Dashboard;

});