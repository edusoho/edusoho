define(function(require, exports, module) {

	require('jquery.sortable');
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $list = $("#userlevel-list").sortable({
			handle: 'span.glyphicon-move',
			containerSelector: "tbody",
			itemSelector: "tr",
			placeholder: '<tr class="placeholder"><td></td><td></td><td></td><td></td><td></td></tr>',
		    onDrop: function (item, container, _super) {

		        _super(item, container);
		        var data = $list.sortable("serialize").get();
		        $.post($list.data('sortUrl'), {ids:data});
		    },
		    serialize: function(parent, children, isContainer) {
		        return isContainer ? children : parent.attr('id');
		    }
		});
	};

});