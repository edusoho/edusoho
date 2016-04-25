define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#nickname-form'
        });

        Validator.addRule(
            'nickname',
            function(options, commit){
                var nickname = options.element.val();
                var reg_nickname = /^1\d{10}$/;
                var result = false;
                var isNickname = reg_nickname.test(nickname);

                if(!isNickname){
                    result = true;
                }
                return result;
            },
                "{{display}}不允许以1开头的11位纯数字"
        );

        validator.addItem({
            element: '[name=nickname]',
            required: true,
            rule : 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} nickname remote'
        });


    };

});