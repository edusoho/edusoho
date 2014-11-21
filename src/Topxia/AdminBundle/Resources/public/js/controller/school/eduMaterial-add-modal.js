define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $('#subject-create-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: '#subject-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#subject-create-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('学科添加成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('学科添加失败');
                });

            }
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        // validator.addItem({
        //     element: '[name="code"]',
        //     required: true,
        //     rule: 'remote'
        // });

    };

});