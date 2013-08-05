define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var QuizPlugin = BasePlugin.extend({
		code: 'quiz',
		name: '测验',
		iconClass: 'glyphicon glyphicon-info-sign',
		api: {
			init: '/lessonplugin/quiz/init',
		},
		execute: function() {	
			var initUrl = this.api.init+"/"+this.toolbar.get('courseId')+"/"+this.toolbar.get('lessonId');
			$('#modal').modal({keyboard: false}).html('').load(initUrl);
		},
	});
	
	module.exports = QuizPlugin;

});