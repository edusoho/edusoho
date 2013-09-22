define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var MaterialPane = require('./pane');

	var MaterialPlugin = BasePlugin.extend({
		code: 'material',
		name: '资料',
		iconClass: 'glyphicon glyphicon-download-alt',
		api: {
			init: '/lessonplugin/material/init'
		},
		execute: function() {
			if (!this.pane) {
				this.pane = new MaterialPane({
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
			console.log("应该有资料的挖");
			if(lesson.materialNum>0){
				$('.glyphicon-download-alt').attr('style','color:#096');
			}else{
				$('.glyphicon-download-alt').removeAttr('style');
			}
		}
	});

	module.exports = MaterialPlugin;

});