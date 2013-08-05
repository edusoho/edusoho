define(function(require, exports, module) {
	var $ = require('jquery');

	var onReady = function(options){

		$('.publish_adv_delete').click(function(){
			if(!confirm('确定要删除吗?')) {
				return false;
			}
		});
	};

	exports.bootstrap = function(options) {
		$(onReady(options));
	};
});
