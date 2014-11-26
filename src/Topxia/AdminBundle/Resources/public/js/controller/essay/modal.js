define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function(){
        var $form = $("#essay-form");
        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);

        function _initValidator($form, $modal) {
                var validator = new Validator({
                element: '#essay-form',
                failSilently: true,
                triggerType: 'change',
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }
                    $('#essay-operate-btn').button('loading').addClass('disabled');
                    Notify.success('操作成功！');
                }
            });

            validator.addItem({
                element: '#essay-title-field',
                required: true
            });

            return validator;
        }



    }
});
