define(function(require, exports, module) {

    var OrderableTable = require('../../common/orderable-table');

	$(function(){

		new OrderableTable({
			element: '#log-table'
		});
 
	});

});