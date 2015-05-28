define(function(require, exports, module) {

	var Widget = require('widget');

	var LessonToolbar = Widget.extend({

		_lessons: {},

		_currentPane: null,

		events: {
			'click .hide-pane': 'hidePane'
		},

		attrs: {
			courseId: null,
			lessonId: null,
			lesson: null,
			activePlugins: {},
			plugins: {}
		},

		registerPlugin: function(plugin) {
			this.get('plugins')[plugin.code] = plugin;
			if (plugin.onRegister) {
				plugin.onRegister();
			}
		},

		addButton: function(name, button) {

		},

		setup: function() {
			var toolbar = this;

			var LessonPlugin = require('./plugins/lesson/plugin');
			var QuestionPlugin = require('./plugins/question/plugin');
			var NotePlugin = require('./plugins/note/plugin');
			var MaterialPlugin = require('./plugins/material/plugin');
			var HomeworkPlugin = require('./plugins/homework/plugin');

			toolbar.registerPlugin(new LessonPlugin(toolbar));
			toolbar.registerPlugin(new QuestionPlugin(toolbar));
			toolbar.registerPlugin(new NotePlugin(toolbar));
			toolbar.registerPlugin(new MaterialPlugin(toolbar));
			toolbar.registerPlugin(new HomeworkPlugin(toolbar));

			var activePlugins = this.get('activePlugins');

			var html = '';
			$.each(toolbar.get('activePlugins'), function(i, name){
				var plugin = toolbar.get('plugins')[name];
				if (plugin.noactive == undefined) {
					plugin.noactive = false;
				}
				html += '<li data-plugin="' + plugin.code + '" data-noactive="' + plugin.noactive + '"><a href="#"><span class="' + plugin.iconClass + '"> </span>' + plugin.name + '</a></li>'
			});

			$('#lesson-toolbar-primary').html(html);
			

			$('#lesson-toolbar-primary').on('click', 'li[data-plugin]', function(e){
				e.preventDefault();
				if ($(this).hasClass('active')) {
					toolbar.hidePane();
					return ;
				}
				$(e.delegateTarget).find('li[data-plugin]').removeClass('active');

				
				var $this = $(this);

				if (!$this.data('noactive')) {
					$this.addClass('active');
				}
				toolbar.get('plugins')[$this.data('plugin')].execute();

				return false;
			});
		},

		getPaneContainer: function() {
			return this.$('.toolbar-pane-container');
		},

		getPane: function(name) {
			$pane = this.getPaneContainer().find('[data-pane=' + name + ']');
			if ($pane.length === 0) {
				return undefined;
			}
			return $pane;
		},

		createPane: function(name) {
			var $pane = this.getPane(name);
			if (!$pane) {
				$pane = $('<div data-pane="' + name + '" class="' + name +'-pane"></div>').appendTo(this.getPaneContainer());
			}
			return $pane;
		},

		showPane: function(name) {
			this.getPaneContainer().find('[data-pane]').hide();
			this.getPaneContainer().find('[data-pane=' + name + ']').show();
			this.getPaneContainer().show();
			this.element.addClass('toolbar-open');
			$('.lesson-dashboard').addClass('lesson-dashboard-open');
			this._currentPane = name;
			this.$('.hide-pane').show();
		},

		hidePane: function() {
			this.getPaneContainer().hide();
			this.$('.toolbar-nav li').removeClass('active');
			this.element.removeClass('toolbar-open');
			$('.lesson-dashboard').removeClass('lesson-dashboard-open');
			this.$('.hide-pane').hide();
		},

		setLessons: function(lessons) {
			if (!$.isEmptyObject(this._lessons)) {
				return ;
			}
			this._lessons = lessons;
			this.trigger('lessons_ready', lessons);
		},

		getLessons: function() {
			return this._lessons;
		},

		_onChangeLessonId: function(id) {
			if (this._currentPane) {
				var plugin = this.get('plugins')[this._currentPane];
				if (plugin.onChangeLesson) {
					plugin.onChangeLesson();
				}
			}
		},

		_onChangeLesson: function(lesson){
			$.each(this.get('plugins'), function(){
				if (this.onChangeMeta) {
					this.onChangeMeta(lesson);
				}

				if (this.onChangeHomeworkOrExercise) {
					this.onChangeHomeworkOrExercise(lesson);
				}
			});
		}
	});

	module.exports = LessonToolbar;

});