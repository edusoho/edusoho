define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var NotePane = require('./pane');

	var NotePlugin = BasePlugin.extend({
		code: 'note',
		name: '笔记',
		iconClass: 'glyphicon glyphicon-edit',
		api: {
			init: '../../lessonplugin/note/init',
			save: '../../lessonplugin/note/save'
		},
		execute: function() {
			if (!this.pane) {
				this.pane = new NotePane({
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

	module.exports = NotePlugin;

});