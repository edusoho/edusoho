define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var QuestionPane = require('./pane');

	var QuestionPlugin = BasePlugin.extend({
		code: 'question',
		name: '问答',
		iconClass: 'glyphicon glyphicon-question-sign',
		api: {
			init: '../../lessonplugin/question/init',
			list: '../../lessonplugin/question/list',
			show: '../../lessonplugin/question/show',
			create: '../../lessonplugin/question/create',
			answer: '../../lessonplugin/question/answer'
		},
		execute: function() {
			if (!this.pane) {
				this.pane = new QuestionPane({
					element: this.toolbar.createPane(this.code),
					plugin: this
				}).render();
			}

			this.pane.show();
		},

		onChangeLesson: function() {
			this.pane.show();
		}

	});

	module.exports = QuestionPlugin;

});