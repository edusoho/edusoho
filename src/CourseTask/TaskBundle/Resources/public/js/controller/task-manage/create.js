define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
    	$("a[data-role='activityType']").click(function(){
    		$('.modal-content', $('#modal')).load($(this).data('url'));
    	});
    };

});
