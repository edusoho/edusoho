define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');


    exports.run = function() {
        var $modal = $('#number-form').parents('.modal');
        var validator = new Validator({
            element: '#number-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    if(html==true){
                        $modal.modal('hide');
                        Notify.success('批量导入学生成功');
                        window.location.reload();
                    }else{
                        Notify.danger(html);
                    }
                }).error(function(){
                    Notify.danger('批量导入学生失败');
                });

            }
        });


        validator.addItem({
            element: '#numbers',
            required: true
        });
	};

});