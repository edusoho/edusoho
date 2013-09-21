define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var QuizPlugin = BasePlugin.extend({
		code: 'quiz',
		name: '测验',
		noactive: true,
		iconClass: 'glyphicon glyphicon-list-alt',
		api: {
			init: '/lessonplugin/quiz/init',
		},
		execute: function() {	
			var initUrl = this.api.init+"/"+this.toolbar.get('courseId')+"/"+this.toolbar.get('lessonId');
			$('#modal').modal({keyboard: false}).html('').load(initUrl);
		},
		onChangeMeta: function(lesson) {	
			if(!lesson){
				return;
			}
			if(lesson.quizNum>0){
				$('.glyphicon-list-alt').attr('style','color:#096');
			}else{
				$('.glyphicon-list-alt').removeAttr('style');
			}
		}
	});
	
	module.exports = QuizPlugin;

});