define(function(require, exports, module) {

  var calculateByteLength = function(string) {
    var length = string.length;
    for ( var i = 0; i < string.length; i++) {
      if (string.charCodeAt(i) > 127)
        length++;
    }
    return length;
  };

  var isDate = function(x){
    return 'undefined' == typeof x.getDate;
  };

  var rules = [
    [
      'es_version',
      /(^\d{1,2}\.\d{1,2}\.\d{1,2})+$/,
      Translator.trans('validate_old.es_version.message', {display:'{{display}}'})
    ],
    [
      'not_all_digital',
      /(^(?![^0-9a-zA-Z]+$))(?![0-9]+$).+/,
      Translator.trans('validate_old.not_all_digital.message', {display:'{{display}}'})
    ], 
    [
      'visible_character',
      function(options) {
        var element = options.element  ;
        if ($.trim(element.val()).length <= 0 )
        { 
          return false;
        } else {
          return true;
        }
      },
      Translator.trans('validate_old.visible_character.message', {display:'{{display}}'})
    ],
    [
      'chinese_limit',
      function(options){
        var element = options.element;
        var l = strlen(element.val());
        return l <= Number(options.max);
      },
      Translator.trans('validate_old.chinese_limit.message', {display: '{{display}}', max: '{{max}}'})
    ],
    [
      'chinese',
      /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i,
      Translator.trans('validate_old.chinese.message', {display: '{{display}}'})
    ],
    [
      'phone', 
      /^1\d{10}$/,
      Translator.trans('validate_old.phone.message', {display: '{{display}}'})
    ],
    [
      'mobile',
      /^1\d{10}$/,
      Translator.trans('validate_old.mobile.message', {display: '{{display}}'})
    ],
    [
      'chinese_alphanumeric',
      /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i,
      Translator.trans('validate_old.chinese_alphanumeric.message', {display: '{{display}}'})
    ],
    [
      'reg_inviteCode',
      /^[a-z0-9A-Z]{5}$/,
      Translator.trans('validate_old.reg_invite_code.message:', {display: '{{display}}'})
    ],
    [
      'alphanumeric',
      /^[a-zA-Z0-9_]+$/i,
      Translator.trans('validate_old.alphanumeric.message', {display: '{{display}}'})
    ],
    [
      'alphabet_underline',
      /^[a-zA-Z_]+[a-zA-Z0-9_]*/i,
      Translator.trans('validate_old.alphabet_underline.message', {display: '{{display}}'})
    ],
    [
      'byte_minlength',
      function(options) {
        var element = options.element;
        var l = calculateByteLength(element.val());
        return l >= Number(options.min);
      },
      Translator.trans('validate_old.byte_minlength.message', {display: '{{display}}', min: '{{min}}'})
    ],
    [
      'currency',
      /^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i,
      Translator.trans('validate_old.currency_check.message', {display: '{{display}}'})
    ],
    [
      'positive_currency',
      /^[0-9]{0,8}(\.\d{0,2})?$/i,
      Translator.trans('validate.positive_currency.message')

    ],

    [
      'byte_maxlength',
      function(options) {
        var element = options.element;
        var l = calculateByteLength(element.val());
        return l <= Number(options.max);
      },
      Translator.trans('validate_old.currency.message', {display: '{{display}}', max: '{{max}}'})
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
        var birth = idcard.substr(6, 4) + '-' + idcard.substr(10, 2) + '-' + idcard.substr(12, 2);
        if (!isDate(birth)) {
          return false;
        }
        var iW = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
        var iSum = 0;
        for (var i = 0; i < 17; i++) {
          var iC = idcard.charAt(i);
          var iVal = parseInt(iC);
          iSum += iVal * iW[i];
        }
        var iJYM = iSum % 11;
        if (iJYM == 0) var sJYM = '1';
        else if (iJYM == 1) sJYM = '0';
        else if (iJYM == 2) sJYM = 'x';
        else if (iJYM == 3) sJYM = '9';
        else if (iJYM == 4) sJYM = '8';
        else if (iJYM == 5) sJYM = '7';
        else if (iJYM == 6) sJYM = '6';
        else if (iJYM == 7) sJYM = '5';
        else if (iJYM == 8) sJYM = '4';
        else if (iJYM == 9) sJYM = '3';
        else if (iJYM == 10) sJYM = '2';
        var cCheck = idcard.charAt(17).toLowerCase();
        if (cCheck != sJYM) {
          return false;
        }
        return true;
      },
      Translator.trans('validate_old.idcard.message', {display: '{{display}}'})
    ],
    [
      'password',
      /^[\S]{4,20}$/i,
      Translator.trans('validate_old.password.message', {display: '{{display}}'})
    ],
    [
      'check_password_low',
      /^[\S]{5,20}$/i,
      Translator.trans('validate.check_password_low.message', {display: '{{display}}'})
    ],
    [
      'check_password_middle',
      /^(?!^(\d+|[a-zA-Z]+|[^\s\da-zA-Z]+)$)^[\S]{8,20}$/i,
      Translator.trans('validate.check_password_middle.message', {display: '{{display}}'})
    ],
    [
      'check_password_high',
      /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^\s\da-zA-Z])[\S]{8,32}$/,
      Translator.trans('validate.check_password_high.message', {display: '{{display}}'})
    ],
    [
      'second_range',
      /^([0-9]|[012345][0-9]|59)$/,
      Translator.trans('validate_old.second_range.message')
    ],
    [
      'date',
      /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/,
      Translator.trans('validate_old.date.message')
    ],
    [
      'qq',
      /^[1-9]\d{4,}$/,
      Translator.trans('validate_old.qq.message', {display: '{{display}}'})
    ],
    [
      'integer',
      /^[+-]?\d+$/,
      Translator.trans('validate_old.integer.message', {display: '{{display}}'})
    ],
    [
      'float',
      /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i,
      Translator.trans('validate_old.float_check.message')
    ],
    [
      'decimal',
      /^(([+]?[1-9]{1}\d*)|([+]?[0]{1}))(\.(\d){1})?$/i,
      Translator.trans('validate_old.float.message')
    ],    
    [
      'int',
      /^[+-]?\d{1,9}$/,
      Translator.trans('validate_old.int.message', {display: '{{display}}'})
    ], 
    [
      'positive_integer',
      /^[1-9]\d*$/,
      Translator.trans('validate_old.positive_integer.message', {display: '{{display}}'})
    ],
    [
      'unsigned_integer',
      /^([1-9]\d*|0)$/,
      Translator.trans('validate_old.unsigned_integer.message', {display: '{{display}}'})
    ],
    [
      'arithmetic_number',
      /^(?!0+(\.0+)?$)\d+(\.\d+)?$/,
      Translator.trans('validate_old.arithmetic_number.message', {display: '{{display}}'})
    ],
    [
      'percent_number',
      /^(100|[1-9]\d|\d)$/,
      Translator.trans('validate_old.percent_number.message')
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
      Translator.trans('validate_old.maxsize_image.message', {display: '{{display}}'})
    ],
    [
      'remote',
      function(options, commit) {
        var element = options.element,
          url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
        $.get(url, {value:element.val()}, function(response) {
          if(typeof(response) == 'object') {
            commit(response.success, Translator.trans(response.message));
          } else if (response === true) {
            commit(response);
          } else {
            commit(false, response);
          }

        }, 'json');
      }
    ],
    [
      'email_remote',
      function(options, commit) {
        var element = options.element,
          url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
        var value = element.val().replace(/\./g, '!');
        $.get(url, {value:value}, function(response) {
          if(typeof(response) == 'object') {
            commit(response.success, Translator.trans(response.message));
          } else if (response === true) {
            commit(response);
          } else {
            commit(false, response);
          }
        }, 'json');
      }
    ],
    [
      'invitecode_remote',
      function(options,commit) {
        var element = options.element,
          url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
        var value = element.val().replace(/\./g, '!');
        $.get(url, {value:value}, function(response) {
          if(typeof(response) == 'object') {
            commit(response.success, Translator.trans(response.message));
          } else if (response === true) {
            commit(response);
          } else {
            commit(false, response);
          }
        }, 'json');
      }
    ],
    [
      'nickname_remote',
      function(options, commit) {
        var element = options.element,
          url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
        var value = element.val().replace(/\./g, '!');
        $.get(url, {value:value, randomName:element.data('randmo')}, function(response) {
          if(typeof(response) == 'object') {
            commit(response.success, Translator.trans(response.message));
          } else if (response === true) {
            commit(response);
          } else {
            commit(false, response);
          }
        }, 'json');
      }
    ],
    [
      'email_or_mobile_remote',
      function(options, commit) {
        var element = options.element,
          url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
        var value = element.val().replace(/\./g, '!');
        $.get(url, {value:value}, function(response) {
          if(typeof(response) == 'object') {
            commit(response.success, Translator.trans(response.message));
          } else if (response === true) {
            commit(response);
          } else {
            commit(false, response);
          }
        }, 'json');
      }
    ],
    [
      'date_check',
      function() {

        var startTime = $('[name=startTime]').val();
        var endTime = $('[name=endTime]').val();
        startTime = startTime.replace(/-/g,'/');
        startTime = Date.parse(startTime)/1000;
        endTime = endTime.replace(/-/g,'/');
        endTime = Date.parse(endTime)/1000;

        if (endTime >= startTime) {
          return true;
        }else{
          return false;
        }
      },
      Translator.trans('validate_old.date_check.message')
    ],
        



    [
      'date_and_time',
      /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/,
      Translator.trans('validate_old.date_and_time.message')
    ],        

    [
      'date_and_time_check',
      function() {
        var startTime = $('[name=startTime]').val();
        var endTime = $('[name=endTime]').val();
        return (startTime < endTime);
      },
      Translator.trans('validate_old.date_and_time_check.message')
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
      Translator.trans('validate_old.deadline_date_check.message')
    ],
    [
      'fixedLength',
      function(options) {
        var element = options.element;
        var l = element.val().length;
        return l == Number(options.len);
      },
      Translator.trans('validate_old.valid_fixed_length_input.message', {display: '{{display}}', length: '{{len}}'})
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
          $('.email_mobile_msg').removeClass('hidden');
        }else {
          $('.email_mobile_msg').addClass('hidden');
        }
        if (isEmail || isMobile) {
          result = true;
        }
        return  result;  
      },
      Translator.trans('validate_old.valid_email_input.message', {display: '{{display}}'})
    ],
    [
      'email',
      /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,
      Translator.trans('validate_old.valid_email_or_mobile_input.message', {display:'{{display}}'})
    ],
    [
      'url',
      /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/,
      Translator.trans('validate_old.valid_url_input.message', {display:'{{display}}'})
    ],
    [
      'number',
      /^[+-]?[1-9][0-9]*(\.[0-9]+)?([eE][+-][1-9][0-9]*)?$|^[+-]?0?\.[0-9]+([eE][+-][1-9][0-9]*)?$/,
      Translator.trans('validate_pld.valid_number_input.message', {display:'{{display}}'})
    ],
    [
      'date',
      /^\d{4}\-[01]?\d\-[0-3]?\d$|^[01]\d\/[0-3]\d\/\d{4}$|^\d{4}年[01]?\d月[0-3]?\d[日号]$/,
      Translator.trans('validate_old.valid_date_input.message', {display:'{{display}}'})
    ],
    [
      'min',
      function(options) {
        var element = options.element, min = options.min;
        return Number(element.val()) >= Number(min);
      },
      Translator.trans('validate_old.min.message', {display:'{{display}}', min:'{{min}}'})
    ],
    [
      'max',
      function(options) {
        var element = options.element, max = options.max;
        return Number(element.val()) <= Number(max);
      },
      Translator.trans('validate_old.max.message', {display:'{{display}}', max:'{{max}}'})
    ],
    [
      'minlength',
      function(options) {
        var element = options.element;
        var l = element.val().length;
        return l >= Number(options.min);
      },
      Translator.trans('validate_old.min_length.message', {display:'{{display}}', min:'{{min}}'})
    ],
    [
      'maxlength',
      function(options) {
        var element = options.element;
        var l = element.val().length;
        return l <= Number(options.max);
      },
      Translator.trans('validate_old.max_length.message', {display:'{{display}}', max:'{{max}}'})
    ],
    [
      'confirmation',
      function(options) {
        var element = options.element, target = $(options.target);
        return element.val() == target.val();
      },
      Translator.trans('validate_old.confirmation.message', {display: '{{display}}'})
    ],
    [
      'editor_maxlength',
      function(options) {
        var value = options.element.val();
        return value.replace(/<\/?[^>]+(>|$)/g, '').replace(/[\r\n]/g,'').length > options.max ? false : true;
      },
      Translator.trans('validate.character_maxlength', {max:'{{max}}'})
    ]
  ];

  var messages = [
    ['required',Translator.trans('validate_old.required.message')],
    ['mobile',Translator.trans('validate_old.mobile.message')]
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
    $.each(messages, function(index,message) {
      Validator.setMessage(message[0],message[1]);
    });
    $.each(rules, function(index, rule){
      Validator.addRule.apply(Validator, rule);
    });
  };
});
