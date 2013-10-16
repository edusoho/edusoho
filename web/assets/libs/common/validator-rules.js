define(function(require, exports, module) {

    var calculateByteLength = function(string) {
        var length = string.length;
        for ( var i = 0; i < string.length; i++) {
            if (string.charCodeAt(i) > 127)
                length++;
        }
        return length;
    }

    var rules = [
        [
            'chinese',
            /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i,
            '{{display}}必须是中文字'
        ],
        [
            'chinese_alphanumeric',
            /^([\u4E00-\uFA29]|[a-zA-Z0-9_])*$/i,
            '{{display}}必须是中文字、英文字母、数字及下划线组成'
        ],
        [
            'alphanumeric',
            /^[a-zA-Z0-9_]+$/i,
            '{{display}}必须是英文字母、数字及下划线组成'
        ],
        [
            'byte_minlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l >= Number(options.min);
            },
            '{{display}}的长度必须大于等于{{min}}，一个中文字算2个字符'
        ],
        [
            'currency',
            /^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i,
            '请输入合法的{{display}},如:200, 221.99, 0.99, 0等'
        ],        
        [
            'byte_maxlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l <= Number(options.max);
            },
            '{{display}}的长度必须小于等于{{max}}，一个中文字算2个字符'
        ],
        [
            'idcard',
            /^\d{17}[0-9xX]$/,
            '{{display}}格式不正确!'
        ],
        [
            'password',
            /^[\S]{4,20}$/i,
            '{{display}}只能由4-20个字符组成'
        ],
        [
            'qq',
            /^[1-9]\d{4,}$/,
            '{{display}}格式不正确'
        ],
        [
            'integer',
            /^[+-]?\d+$/,
            '{{display}}必须为整数'
        ],
        [
            'remote',
            function(options, commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                $.get(url, {value:element.val()}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ]
    ];

    exports.inject = function(Validator) {
        $.each(rules, function(index, rule){
            Validator.addRule.apply(Validator, rule);
        });

    }

});
