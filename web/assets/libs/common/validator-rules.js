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
            'not_all_digital',
            /(^(?![^0-9a-zA-Z]+$))(?![0-9]+$).+/,
            Translator.trans('%display%不能全为数字', {display:'{{display}}'})
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
            Translator.trans('%display%请输入可见性字符', {display:'{{display}}'})
        ],
        [
            'chinese_limit',
            function(options){
                var element = options.element;
                var l = strlen(element.val());
                return l <= Number(options.max);
            },
            Translator.trans('%display%的长度必须小于等于%max%字符,一个中文为2个字符', {display: '{{display}}', max: '{{max}}'})
        ],
        [
            'chinese',
            /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i,
            Translator.trans('%display%必须是中文字', {display: '{{display}}'})
        ],
        [
            'phone', 
            /^1\d{10}$/,
            Translator.trans('请输入有效的%display%', {display: '{{display}}'})
        ],
        [
            'chinese_alphanumeric',
            /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i,
            Translator.trans('%display%必须是中文字、英文字母、数字及特殊符号_ . ·组成', {display: '{{display}}'})
        ],
        [
           'reg_inviteCode',
            /^[a-z0-9A-Z]{5}$/,
            Translator.trans('%display%必须是5位数字、英文字母组成', {display: '{{display}}'})
        ],
        [
            'alphanumeric',
            /^[a-zA-Z0-9_]+$/i,
            Translator.trans('%display%必须是英文字母、数字及下划线组成', {display: '{{display}}'})
        ],
        [
            'alphabet_underline',
            /^[a-zA-Z_]+[a-zA-Z0-9_]*/i,
            Translator.trans('%display%必须以英文字母或下划线开头', {display: '{{display}}'})
        ],
        [
            'byte_minlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l >= Number(options.min);
            },
            Translator.trans('%display%的长度必须大于等于%min%，一个中文字算2个字符', {display: '{{display}}', min: '{{min}}'})
        ],
        [
            'currency',
            /^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i,
            Translator.trans('请输入合法的%display%,如:200, 221.99, 0.99, 0等', {display: '{{display}}'})
        ],      
        [
            'byte_maxlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l <= Number(options.max);
            },
            Translator.trans('%display%的长度必须小于等于%max%，一个中文字算2个字符', {display: '{{display}}', max: '{{max}}'})
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
            Translator.trans('%display%格式不正确', {display: '{{display}}'})
        ],
        [
            'password',
            /^[\S]{4,20}$/i,
            Translator.trans('%display%只能由4-20个字符组成', {display: '{{display}}'})
        ],
        [
            'second_range',
            /^([0-9]|[012345][0-9]|59)$/,
            Translator.trans('秒数只能在0-59之间')
        ],
        [
            'date',
            /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/,
            Translator.trans('请输入正确的日期,格式如XXXX-MM-DD')
        ],
        [
            'qq',
            /^[1-9]\d{4,}$/,
            Translator.trans('%display%格式不正确', {display: '{{display}}'})
        ],
        [
            'integer',
            /^[+-]?\d+$/,
            Translator.trans('%display%必须为整数', {display: '{{display}}'})
        ],
        [
            'float',
            /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i,
            Translator.trans('请输入正确的小数,只保留到两位小数')
        ],
        [
            'decimal',
            /^(([+]?[1-9]{1}\d*)|([+]?[0]{1}))(\.(\d){1})?$/i,
            Translator.trans('请输入正确的小数,只保留到一位小数')
        ],    
        [
            'int',
            /^[+-]?\d{1,9}$/,
            Translator.trans('%display%必须为整数,最大到9位整数', {display: '{{display}}'})
        ], 
        [
            'positive_integer',
            /^[1-9]\d*$/,
            Translator.trans('%display%必须为正整数', {display: '{{display}}'})
        ],
        [
            'unsigned_integer',
            /^([1-9]\d*|0)$/,
            '{{display}}必须为非负整数'
        ],
        [
            'arithmetic_number',
            /^(?!0+(\.0+)?$)\d+(\.\d+)?$/,
            Translator.trans('%display%必须为正数', {display: '{{display}}'})
        ],
        [
            'percent_number',
            /^(100|[1-9]\d|\d)$/,
            Translator.trans('必须在0~100之间')
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
            Translator.trans('{{display}}必须小于2M', {display: '{{display}}'})
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
            'invitecode_remote',
            function(options,commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                    value = element.val().replace(/\./g, "!");
                $.get(url, {value:value}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ],
        [
            'nickname_remote',
            function(options, commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                    value = element.val().replace(/\./g, "!");
                $.get(url, {value:value, randomName:element.data('randmo')}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ],
        [
            'email_or_mobile_remote',
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
            },
            Translator.trans('开始时间必须小于或等于结束时间')
        ],
        



        [
            'date_and_time',
            /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/,
            Translator.trans('请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm:ss')
        ],        

        [
            'date_and_time_check',
            function() {
                var startTime = $('[name=startTime]').val();
                var endTime = $('[name=endTime]').val();
                return (startTime < endTime);
            },
            Translator.trans('结束时间不能早于或等于开始时间')
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
            },
            Translator.trans('有效期必须大于等于当前日期')
        ],
        [
            'fixedLength',
            function(options) {
                var element = options.element;
                var l = element.val().length;
                return l == Number(options.len);
            },
            Translator.trans('%display%的长度必须等于%length%', {display: '{{display}}', length: '{{len}}'})
        ],
        [
            'email_or_mobile',
             function(options){
               var emailOrMobile = options.element.val();
               var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
               var reg_mobile = /^1\d{10}$/;
               var result =false;
               var isEmail = reg_email.test(emailOrMobile);
               var isMobile = reg_mobile.test(emailOrMobile);
               if(isMobile){
                    $(".email_mobile_msg").removeClass('hidden');
               }else {
                    $(".email_mobile_msg").addClass('hidden');
               }
               if (isEmail || isMobile) {
                    result = true;
                }
                return  result;  
             },
            Translator.trans('%display%格式错误', {display: '{{display}}'})
        ],
        [
            'mobile',
            /^1\d{10}$/,
            Translator.trans('请输入正确的%display%', {display:'{{display}}'})
        ],
        [
            'email',
            /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,
            Translator.trans('%display%的格式不正确', {display:'{{display}}'})
        ],
        [
            'url',
            /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/,
            Translator.trans('%display%的格式不正确', {display:'{{display}}'})
        ],
        [
            'number',
            /^[+-]?[1-9][0-9]*(\.[0-9]+)?([eE][+-][1-9][0-9]*)?$|^[+-]?0?\.[0-9]+([eE][+-][1-9][0-9]*)?$/,
            Translator.trans('%display%的格式不正确', {display:'{{display}}'})
        ],
        [
            'date',
            /^\d{4}\-[01]?\d\-[0-3]?\d$|^[01]\d\/[0-3]\d\/\d{4}$|^\d{4}年[01]?\d月[0-3]?\d[日号]$/,
            Translator.trans('%display%的格式不正确', {display:'{{display}}'})
        ],
        [
           'min',
            function(options) {
                var element = options.element, min = options.min;
                return Number(element.val()) >= Number(min);
            },
            Translator.trans('%display%必须大于或者等于%min%', {display:'{{display}}', min:'{{min}}'})
        ],
        [
            'max',
            function(options) {
                var element = options.element, max = options.max;
                return Number(element.val()) <= Number(max);
            },
            Translator.trans('%display%必须小于或者等于%max%', {display:'{{display}}', max:'{{max}}'})
        ],
        [
            'minlength',
            function(options) {
                var element = options.element;
                var l = element.val().length;
                return l >= Number(options.min);
            },
            Translator.trans('%display%的长度必须大于或等于%min%', {display:'{{display}}', min:'{{min}}'})
        ],
        [
            'maxlength',
            function(options) {
                var element = options.element;
                var l = element.val().length;
                return l <= Number(options.max);
            },
            Translator.trans('%display%的长度必须小于或等于%max%', {display:'{{display}}', max:'{{max}}'})
        ],
        [
            'confirmation',
            function(options) {
                var element = options.element, target = $(options.target);
                return element.val() == target.val();
            },
            Translator.trans('两次输入的%display%不一致，请重新输入', {display: '{{display}}'})
        ]
    ];

    function strlen(str){  
        var len = 0;  
        for (var i=0; i<str.length; i++) {   
            var chars = str.charCodeAt(i);   
            //单字节加1   
            if ((chars >= 0x0001 && chars <= 0x007e) || (0xff60<=chars && chars<=0xff9f)) {   
               len++;   
            } else {   
               len+=2;   
            }   
        }   
        return len;  
    }

    exports.inject = function(Validator) {
        $.each(rules, function(index, rule){
            Validator.addRule.apply(Validator, rule);
        });

    }

});
