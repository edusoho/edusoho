define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var LectureNotePane = require('./pane');

	var LectureNotePlugin = BasePlugin.extend({
		code: 'lecture-note',
		name: '讲义',
		iconClass: 'glyphicon glyphicon-book',
		api: {
			init: '../../lessonplugin/lecture/note/init',
		},
		execute: function() {
			if (!this.pane) {
				this.pane = new LectureNotePane({
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
		onChangeMeta: function(lesson) {	
			if(!lesson){
				return;
			}
			if(lesson.materialNum>0){
				$('.glyphicon-download-alt').addClass('text-success');
			}else{
				$('.glyphicon-download-alt').removeClass('text-success');
			}
		}
	});

	module.exports = LectureNotePlugin;

});