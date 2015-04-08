define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var HomeworkPane = require('./pane');

	var HomeworkPlugin = BasePlugin.extend({
		code: 'homework',
		name: '作业',
		iconClass: 'glyphicon glyphicon-list-alt',
		api: {
			list: '../../lessonplugin/homework/list'
		},
		execute: function() {
			if (!this.pane) {
				this.pane = new HomeworkPane({
					element: this.toolbar.createPane(this.code),
					code: this.code,
					toolbar: this.toolbar,
					plugin: this
				}).render();
			}
			this.pane.show();
		},
		onChangeLesson: function() {
			this.pane.show();
		},
		onChangeHomeworkOrExercise: function(lesson) {	
			if(!lesson){
				return;
			}
			if(lesson.homeworkOrExerciseNum>0){
				$('.glyphicon-list-alt').addClass('text-success');
				$('.toolbar-nav .glyphicon-list-alt').html('<span class="badge" style="padding: 1px 5px;position:absolute;font-style: normal;background-color: #f9534f;">'+lesson.homeworkOrExerciseNum+'</span>');
			}else{
				$('.glyphicon-list-alt').removeClass('text-success');
				$('.badge').remove();
			}
		}
		
	});

	module.exports = HomeworkPlugin;

});