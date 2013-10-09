define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var LessonPane = require('./pane');

	var LessonPlugin = BasePlugin.extend({
		code: 'lesson',
		name: '目录',
		iconClass: 'glyphicon glyphicon-th-list',
		api: {
			list: '../../lessonplugin/lesson/list'
		},
		execute: function() {
			this.pane.show();
		},
		onRegister: function() {
			this.pane = new LessonPane({
				element: this.toolbar.createPane(this.code),
				toolbar: this.toolbar,
				plugin: this
			}).render();
		}
	});

	module.exports = LessonPlugin;

});