define(function(require, exports, module) {

	var BasePlugin = require('../base-plugin');

	var MaterialPane = require('./pane');

	var MaterialPlugin = BasePlugin.extend({
		code: 'material',
		name: '资料',
		iconClass: 'glyphicon glyphicon-download-alt',
		api: {
			init: '../../lessonplugin/material/init'
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
			if(lesson.materialNum>0){
				$('.glyphicon-download-alt').addClass('text-success');
				$('.toolbar-nav .glyphicon-download-alt').html('<span class="showNumber" style="display: inline-block;min-width: 10px;padding: 3px 7px;font-size: 12px;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap;vertical-align: baseline;background-color: #999;border-radius: 10px;padding: 1px 5px;position:absolute;font-style: normal;background-color: #f9534f;">'+lesson.materialNum+'</span>');
			}else{
				$('.glyphicon-download-alt').removeClass('text-success');
				$('.showNumber').remove();
			}
		}
	});

	module.exports = MaterialPlugin;

});