    define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {

        var editor = CKEDITOR.replace('profile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
        });

        $('.text').each(function() {
            var id = $(this).attr('id');
            CKEDITOR.replace(id, {
                toolbar: 'Simple',
                filebrowserImageUploadUrl: $(this).data('imageUploadUrl'),
                height: 100
            });
        });

        $(".date").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

        var validator = new Validator({
            element: '#user-profile-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#profile-save-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="profile[truename]"]',
            rule: 'chinese minlength{min:2} maxlength{max:12}'
        });

        validator.addItem({
            element: '[name="profile[qq]"]',
            rule: 'qq'
        });

        validator.addItem({
            element: '[name="profile[weibo]"]',
            rule: 'url',
            errormessageUrl: '微博地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="profile[blog]"]',
            rule: 'url',
            errormessageUrl: '博客地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="profile[site]"]',
            rule: 'url',
            errormessageUrl: '个人主页地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="profile[mobile]"]',
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="profile[idcard]"]',
            rule: 'idcard'
        });


        for(var i=1;i<=5;i++){
             validator.addItem({
             element: '[name="profile[intField'+i+']"]',
             rule: 'int'
             });

             validator.addItem({
            element: '[name="profile[floatField'+i+']"]',
            rule: 'float'
            });

             validator.addItem({
            element: '[name="profile[dateField'+i+']"]',
            rule: 'date'
             });
        }

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        if ($('.form-iam-group').length>=1) {

            var iam = $('.form-iam-group').find('input[type=radio]:checked').val();

            $('.form-forIam-group').hide();

            $('.form-'+ iam +'-group').show();

            $('.form-iam-group').on('change', 'input[type=radio]', function() {
                iam = $(this).val();
                $('.form-forIam-group').hide();
                $('.form-'+ iam +'-group').show();
            });
        }

    };

});