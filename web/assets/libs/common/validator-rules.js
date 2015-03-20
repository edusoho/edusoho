define(function(require, exports, module) {

    var calculateByteLength = function(string) {
        var length = string.length;
        for ( var i = 0; i < string.length; i++) {
            if (string.charCodeAt(i) > 127)
                length++;
        }
        return length;
    }

    var isDate = function(x){
        return "undefined" == typeof x.getDate;
    }

    var rules = [
        [
            'integer',
            /[0-9]*/,
            '{{display}}必须是数字'
        ],
        [
            'not_all_digital',
            /(^(?![^0-9a-zA-Z]+$))(?![0-9]+$).+/,
            '{{display}}不能全为数字'
        ], 
        [
            'visible_character',
            function(options) {
                var element = options.element  ;
                if ($.trim(element.val()).length <= 0 )
                { 
                    return false
                } else {
                    return true;
                }
            },
            '{{display}}请输入可见性字符'
        ],
        [
            'chinese',
            /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i,
            '{{display}}必须是中文字'
        ],
        [
            'phone', 
            /^1\d{10}$/,
            '请输入有效的{{display}}'
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
            'alphabet_underline',
            /^[a-zA-Z_]+[a-zA-Z0-9_]*/i,
            '{{display}}必须以英文字母或下划线开头'
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
        function(options){
        var idcard = options.element.val();
            var reg = /^\d{17}[0-9xX]$/i;
            if (!reg.test(idcard)) {
                return false;
            }
            var n = new Date();
            var y = n.getFullYear();
            if (parseInt(idcard.substr(6, 4)) < 1900 || parseInt(idcard.substr(6, 4)) > y) {
                return false;
            }
            var birth = idcard.substr(6, 4) + "-" + idcard.substr(10, 2) + "-" + idcard.substr(12, 2);
            if (!isDate(birth)) {
                return false;
            }
            iW = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
            iSum = 0;
            for (i = 0; i < 17; i++) {
                iC = idcard.charAt(i);
                iVal = parseInt(iC);
                iSum += iVal * iW[i];
            }
            iJYM = iSum % 11;
            if (iJYM == 0) sJYM = "1";
            else if (iJYM == 1) sJYM = "0";
            else if (iJYM == 2) sJYM = "x";
            else if (iJYM == 3) sJYM = "9";
            else if (iJYM == 4) sJYM = "8";
            else if (iJYM == 5) sJYM = "7";
            else if (iJYM == 6) sJYM = "6";
            else if (iJYM == 7) sJYM = "5";
            else if (iJYM == 8) sJYM = "4";
            else if (iJYM == 9) sJYM = "3";
            else if (iJYM == 10) sJYM = "2";
            var cCheck = idcard.charAt(17).toLowerCase();
            if (cCheck != sJYM) {
                return false;
            }
            return true;
        },
        '{{display}}格式不正确'
        ],
        [
            'password',
            /^[\S]{4,20}$/i,
            '{{display}}只能由4-20个字符组成'
        ],
        [
            'second_range',
            /^([0-9]|[012345][0-9]|59)$/,
            '秒数只能在0-59之间'
        ],
        [
            'date',
            /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/,
            '请输入正确的日期,格式如XXXX-MM-DD'
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
            'float',
            /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i,
            '请输入正确的小数,只保留到两位小数'
        ],
        [
            'decimal',
            /^(([+]?[1-9]{1}\d*)|([+]?[0]{1}))(\.(\d){1})?$/i,
            '请输入正确的小数,只保留到一位小数'
        ],    
        [
            'int',
            /^[+-]?\d{1,9}$/,
            '{{display}}必须为整数,最大到9位整数'
        ], 
        [
            'positive_integer',
            /^[0-9]*[1-9][0-9]*$/,
            '{{display}}必须为正整数'
        ],
        [
            'arithmetic_number',
            /^(?!0+(\.0+)?$)\d+(\.\d+)?$/,
            '{{display}}必须为正数'
        ],
        [
            'maxsize_image',
            function (options) {
                var element = options.element;
                if (!window.ActiveXObject){
                    var image_size = element[0].files[0].size;
                    image_size = image_size / 1048576;
                    return image_size <= 5;
                } else {
                    return true;
                }
            },
            '{{display}}必须小于2M'
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
        ],
        [
            'email_remote',
            function(options, commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                    value = element.val().replace(/\./g, "!");
                $.get(url, {value:value}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ],
        [
            'date_check',
            function() {

                var startTime = $('[name=startTime]').val();
                var endTime = $('[name=endTime]').val();
                startTime = startTime.replace(/-/g,"/");
                startTime = Date.parse(startTime)/1000;
                endTime = endTime.replace(/-/g,"/");
                endTime = Date.parse(endTime)/1000;

                if (endTime >= startTime) {
                    return true;
                }else{
                    return false;
                }
            },"开始时间必须小于或等于结束时间"
        ],
        [
            'deadline_date_check',
            function(opt) {
                var now = new Date;
                var v = opt.element.val();

                if( parseInt(now.getFullYear()) > parseInt(v.split('-')[0]) ){
                    return false;
                }else if( parseInt(now.getFullYear()) < parseInt(v.split('-')[0]) ){
                    return true;
                }
                
                if( parseInt(now.getMonth()+1) > parseInt(v.split('-')[1]) ){
                    return false;
                }else if( parseInt(now.getMonth()+1) < parseInt(v.split('-')[1]) ){
                    return true;
                }
                if( parseInt(now.getDate()) > parseInt(v.split('-')[2]) ){
                    return false;
                }else if( parseInt(now.getDate()) < parseInt(v.split('-')[2]) ){
                    return true;
                }
                return true;
            },"有效期必须大于等于当前日期"
        ],
        [
            'fixedLength',
            function(options) {
                var element = options.element;
                var l = element.val().length;
                return l == Number(options.len);
            },"{{display}}的长度必须等于{{len}}"
        ]        
    ];

    exports.inject = function(Validator) {
        $.each(rules, function(index, rule){
            Validator.addRule.apply(Validator, rule);
        });

    }

});
