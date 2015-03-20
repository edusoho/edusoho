define(function(require, exports, module) {

	var Widget = require('widget');

	var HomeworkPane = Widget.extend({
		attrs: {
            plugin: null
        },
        events: {},
		setup: function() {},
		show: function() {
			this.get('plugin').toolbar.showPane(this.get('plugin').code);
			var pane = this,
            toolbar = pane.get('plugin').toolbar;
            $.get(pane.get('plugin').api.list, {
                courseId: toolbar.get('courseId'),
                lessonId: toolbar.get('lessonId')
            }, function(html) {
            	pane.element.html(html);
            });

		}
	});

	module.exports = HomeworkPane;

});