define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#approval-form'
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule: 'idcard'
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese byte_minlength{min:4} byte_maxlength{max:50}'
        });

        validator.addItem({
            element: '[name="faceImg"]',
            required: true,
            rule: 'isImage limitSize'
        });

        validator.addItem({
            element: '[name="backImg"]',
            required: true,
            rule: 'isImage limitSize'
        });

        Validator.addRule('isImage', function(options) {
           
            if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
                return true;
            }
            var file = options.element[0]['files'][0];
            var types = file['type'].split('/');
            return types[0] == 'image';
        }, '{{display}}只能上传图片');

        Validator.addRule('limitSize', function(options) {
            if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
                return true;
            }
            var file = options.element[0]['files'][0];
            return file['size'] / 1024 <= 2048;
        }, '{{display}}大小不能超过2M');
    };

});