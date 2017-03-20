define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

	exports.run = function() {

		var $form = $('#delete-form');
		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('.js-delete-btn').button('loading');
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    if(response.success){
                        $.post($('#delete-btn').data('url'), function(resp){
                            if(resp.code == 0){
                                Notify.success(Translator.trans('删除课程成功'));
                                location.reload();
                            }else{
                                Notify.success(Translator.trans('删除课程失败：' + resp.message));
                            }
                        });
                    }else{
                        $('#delete-form').children('div').addClass('has-error');
                        $('#delete-form').find('.help-block').show().text(Translator.trans('验证密码错误'));
                    }
                });
            }
        });

        validator.addItem({
            element: '[name=password]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display:Translator.trans('密码')
        });
	}
});
