define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		for (var i = 4; i >= 1; i--) {
            var id = '#article-property-tips'+i;
            var htmlId = id + '-html';
            $(id).popover({
                html: true,
                trigger: 'hover',//'hover','click'
                placement: 'left',//'bottom',
                content: $(htmlId).html()
            });
        };
        $("input[name='email-status']").data('status')=="used" ?$("[name='warning']").hide():"";
        $("[name='sign-update']").on('click',function(){
        	$("[name='submit-sign']").show();
        	$("[name='status']").hide();
            var validator = new Validator({
                element: '#email-form'
            });
            validator.addItem({
                element: '[name="sign"]',
                required: true,
                rule:'chinese_alphanumeric minlength{min:3} maxlength{max:8}',
                display: "签名",
                errormessageRequired: '签名3-8字，建议使用汉字'
            });
        });

	}
	
});