define(function(require, exports, module) {

    require('jquery.perfect-scrollbar');

	var Widget = require('widget');

	var MaterialPane = Widget.extend({
		attrs: {
            plugin: null
        },
        events: {},
		setup: function() {},
		show: function() {
			this.get('plugin').toolbar.showPane(this.get('plugin').code);
			var pane = this,
            toolbar = pane.get('plugin').toolbar;
            $.get(pane.get('plugin').api.init, {
                courseId: toolbar.get('courseId'),
                lessonId: toolbar.get('lessonId')
            }, function(html) {
            	
            	pane.element.html(html);
            	$('.material-pane').perfectScrollbar({wheelSpeed:50});
            });

		}
	});

	module.exports = MaterialPane;

});