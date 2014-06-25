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
		}
		
	});

	module.exports = HomeworkPlugin;

});