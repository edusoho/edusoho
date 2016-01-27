define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		for (var i = 2; i >= 1; i--) {
            var id = '#article-property-tips'+i;
            var htmlId = id + '-html';
            $(id).popover({
                html: true,
                trigger: 'hover',//'hover','click'
                placement: 'left',//'bottom',
                content: $(htmlId).html()
            });
        };
	}
	
});