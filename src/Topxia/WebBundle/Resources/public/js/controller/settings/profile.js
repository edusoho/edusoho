define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {

        var editor = EditorFactory.create('#profile_about', 'simple', {extraFileUploadParams:{group:'user'}});

        var validator = new Validator({
            element: '#user-profile-form',
            failSilently: true
        });

        validator.addItem({
            element: '[name="profile[truename]"]',
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="profile[qq]"]',
            rule: 'qq'
        });

        validator.addItem({
            element: '[name="profile[weibo]"]',
            rule: 'url',
        });

        validator.addItem({
            element: '[name="profile[blog]"]',
            rule: 'url',
            errormessageUrl: '博客地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="profile[site]"]',
            rule: 'url',
            errormessageUrl: '网站地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="profile[mobile]"]',
            rule: 'phone'
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});