import 'jquery-validation';
import { isEmpty } from 'common/utils';
import  { axis } from 'common/axis';

$.validator.setDefaults({
  errorClass: 'form-error-message jq-validate-error',
  errorElement: 'p',
  onkeyup: false,
  ignore: '',
  ajax: false,
  currentDom: null,
  highlight: function(element, errorClass, validClass) {
    let $row = $(element).addClass('form-control-error').closest('.form-group').addClass('has-error');
    $row.find('.help-block').hide();
  },
  unhighlight: function(element, errorClass, validClass) {
    let $row = $(element).removeClass('form-control-error').closest('.form-group');
    $row.removeClass('has-error');
    $row.find('.help-block').show();
  },
  errorPlacement: function(error, element) {
    if (element.parent().hasClass('controls')) {
      element.parent('.controls').append(error);
    } else if (element.parent().hasClass('input-group')) {
      element.parent().after(error);
    } else if (element.parent().is('label')) {
      element.parent().parent().append(error);
    } else {
      element.parent().append(error);
    }
  },
  invalidHandler: function(data, validator) {
    const errorNum = validator.numberOfInvalids();
    if (errorNum) {
      $(validator.errorList[0].element).focus();
    }
    console.log(data);
  },
  submitError: function(data) {
    console.log('submitError');
  },
  submitSuccess: function(data) {
    console.log('submitSuccess');
  },
  submitHandler: function(form) {
    console.log('submitHandler');
    //规定不要用模态框 submit按钮（<input type=’submit’>）提交表单；
    let $form = $(form);
    let settings = this.settings;
    let $btn = $(settings.currentDom);
    if (!$btn.length) {
      $btn = $(form).find('[type="submit"]');
    }
    $btn.button('loading');
    if (settings.ajax) {
      $.post($form.attr('action'), $form.serializeArray(), (data) => {
        $btn.button('reset');
        settings.submitSuccess(data);
      }).error((data) => {
        $btn.button('reset');
        settings.submitError(data);
      });
    } else {
      form.submit();
    }
  }
});

$.extend($.validator.prototype, {
  defaultMessage: function(element, rule) {
    if (typeof rule === 'string') {
      rule = { method: rule };
    }

    var message = this.findDefined(
        this.customMessage(element.name, rule.method),
        this.customDataMessage(element, rule.method),

        // 'title' is never undefined, so handle empty string as undefined
        !this.settings.ignoreTitle && element.title || undefined,
        $.validator.messages[rule.method],
        '<strong>Warning: No message defined for ' + element.name + '</strong>'
      ),
      theregex = /\$?\{(\d+)\}/g,
      displayregex = /%display%/g;
    if (typeof message === 'function') {
      message = message.call(this, rule.parameters, element);
    } else if (theregex.test(message)) {
      message = $.validator.format(message.replace(theregex, '{$1}'), rule.parameters);
    }

    if (displayregex.test(message)) {
      var labeltext, name;
      var id = $(element).attr('id') || $(element).attr('name');
      if (id) {
        labeltext = $('label[for=' + id + ']').text();
        if (labeltext) {
          labeltext = labeltext.replace(/^[\*\s\:\：]*/, '').replace(/[\*\s\:\：]*$/, '');
        }
      }

      name = $(element).data('display') || $(element).attr('name');
      message = message.replace(displayregex, labeltext || name);
    }

    return message;
  }

});

$.extend($.validator.messages, {
  required: Translator.trans('validate.required.message'),
  remote: '请修正此字段',
  email: Translator.trans('validate.valid_email_input.message'),
  url: Translator.trans('validate.valid_url_input.message'),
  date: Translator.trans('validate.valid_date_input.message'),
  dateISO: Translator.trans('validate.valid_date_iso_input.message'),
  number: Translator.trans('validate.valid_number_input.message'),
  digits: Translator.trans('validate.valid_digits_input.message'),
  creditcard: Translator.trans('validate.valid_creditcard_input.message'),
  equalTo: Translator.trans('validate.valid_equal_to_input.message'),
  extension: Translator.trans('validate.valid_extension_input.message'),
  maxlength: $.validator.format(Translator.trans('validate.max_length.message')),
  minlength: $.validator.format(Translator.trans('validate.min_length.message')),
  rangelength: $.validator.format(Translator.trans('validate.range_length.message')),
  range: $.validator.format(Translator.trans('validate.range.message')),
  max: $.validator.format(Translator.trans('validate.max.message')),
  min: $.validator.format(Translator.trans('validate.min.message'))
});

$.validator.addMethod('DateAndTime', function(value, element) {
  let reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
  return this.optional(element) || reg.test(value);
}, $.validator.format(Translator.trans('validate.valid_date_and_time_input.message')));

function strlen(str) {
  let len = 0;
  for (let i = 0; i < str.length; i++) {
    let chars = str.charCodeAt(i);
    //单字节加1
    if ((chars >= 0x0001 && chars <= 0x007e) || (0xff60 <= chars && chars <= 0xff9f)) {
      len++;
    } else {
      len += 2;
    }
  }
  return len;
}

$.validator.addMethod('trim', function(value, element, params) {
  return this.optional(element) || $.trim(value).length > 0;
}, Translator.trans('validate.trim.message'));

$.validator.addMethod('visible_character', function(value, element, params) {
  return this.optional(element) || (value.match(/\S/g).length === value.length);
}, Translator.trans('validate.visible_character.message'));

$.validator.addMethod('idcardNumber', function(value, element, params) {
  let _check = function(idcardNumber) {
    let reg = /^\d{17}[0-9xX]$/i;
    if (!reg.test(idcardNumber)) {
      return false;
    }
    let n = new Date();
    let y = n.getFullYear();
    if (parseInt(idcardNumber.substr(6, 4)) < 1900 || parseInt(idcardNumber.substr(6, 4)) > y) {
      return false;
    }
    let birth = idcardNumber.substr(6, 4) + '-' + idcardNumber.substr(10, 2) + '-' + idcardNumber.substr(12, 2);
    if (!'undefined' == typeof birth.getDate) {
      return false;
    }
    let IW = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
    let iSum = 0;
    for (let i = 0; i < 17; i++) {
      iSum += parseInt(idcardNumber.charAt(i)) * IW[i];
    }
    let iJYM = iSum % 11;
    let sJYM = '';
    if (iJYM == 0) sJYM = '1';
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
    let cCheck = idcardNumber.charAt(17).toLowerCase();
    if (cCheck != sJYM) {
      return false;
    }
    return true;
  };
  return this.optional(element) || _check($.trim(value));
}, Translator.trans('validate.idcard_number_input.message'));

$.validator.addMethod('visible_character', function(value, element, params) {
  return this.optional(element) || $.trim(value).length > 0;
}, Translator.trans('validate.visible_character_input.message'));

$.validator.addMethod('positive_integer', function(value, element, params = true) {
  if (!params) {
    return true;
  }
  return this.optional(element) || /^\+?[1-9][0-9]*$/.test(value);
}, Translator.trans('validate.positive_integer.message'));


$.validator.addMethod('unsigned_integer', function(value, element) {
  return this.optional(element) || /^\+?[0-9][0-9]*$/.test(value);
}, Translator.trans('validate.unsigned_integer.message'));

// jQuery.validator.addMethod("unsigned_integer", function (value, element) {
//   return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
// }, "时长必须为非负整数");

jQuery.validator.addMethod('second_range', function(value, element) {
  return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, Translator.trans('validate.second_range.message'));

$.validator.addMethod('course_title', function(value, element, params) {
  return this.optional(element) || /^[^<>]*$/.test(value);
}, Translator.trans('validate.course_title.message'));

$.validator.addMethod('float', function(value, element) {
  return this.optional(element) || /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i.test(value);
}, Translator.trans('validate.float_input.message'));

$.validator.addMethod('date', function(value, element) {
  return this.optional(element) || /^\d{4}\-[01]?\d\-[0-3]?\d$|^[01]\d\/[0-3]\d\/\d{4}$|^\d{4}年[01]?\d月[0-3]?\d[日号]$/.test(value);
}, Translator.trans('validate.valid_date_input.message'));

$.validator.addMethod('open_live_course_title', function(value, element, params) {
  return this.optional(element) || /^[^<|>|'|"|&|‘|’|”|“]*$/.test(value);
}, Translator.trans('validate.open_live_course_title.message'));

$.validator.addMethod('currency', function(value, element, params) {
  return this.optional(element) || /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
}, Translator.trans('validate.currency.message'));

//@TODO这里不应该判断大于0，应该用组合positive_currency:true，min:1，看到替换
$.validator.addMethod('positive_currency', function(value, element, params) {
  return value > 0 && /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
}, Translator.trans('validate.positive_currency.message'));

$.validator.addMethod('positive_price', function(value, element, params) {
  return /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
}, Translator.trans('validate.positive_currency.message'));

jQuery.validator.addMethod('max_year', function(value, element) {
  return this.optional(element) || value < 100000;
}, Translator.trans('validate.max_year.message'));

$.validator.addMethod('check_password_low', function (value, element) {
  return this.optional(element) || /^[\S]{5,20}$/u.test(value);
}, Translator.trans('validate.check_password_low.message'));

$.validator.addMethod('check_password_middle', function (value, element) {
  return this.optional(element) || /^(?!^(\d+|[a-zA-Z]+|[^\s\da-zA-Z]+)$)^[\S]{8,20}$/.test(value);
}, Translator.trans('validate.check_password_middle.message'));

$.validator.addMethod('check_password_high', function (value, element) {
  return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^\s\da-zA-Z])[\S]{8,32}$/.test(value);
}, Translator.trans('validate.check_password_high.message'));

$.validator.addMethod('before_date', function(value, element, params) {
  let date = new Date(value);
  let afterDate = new Date($(params).val());
  return this.optional(element) || afterDate >= date;
},
Translator.trans('validate.before_date.message')
);

$.validator.addMethod('after_date', function(value, element, params) {
  let date = new Date(value);
  let afterDate = new Date($(params).val());
  return this.optional(element) || afterDate <= date;
},
Translator.trans('validate.after_date.message')
);

$.validator.addMethod('after_now', function(value, element, params) {
  let afterDate = new Date(value.replace(/-/g, '/')); //fix sf;
  return this.optional(element) || afterDate >= new Date();
},
Translator.trans('validate.after_now.message')
);

//日期比较，不进行时间比较
$.validator.addMethod('after_now_date', function(value, element, params) {
  let now = new Date();
  let afterDate = new Date(value);
  let str = now.getFullYear() + '/' + (now.getMonth() + 1) + '/' + now.getDate();
  return this.optional(element) || afterDate >= new Date(str);
},
Translator.trans('validate.after_now_date.message')
);

//检查将废除,没有严格的时间转换，有兼容问题
$.validator.addMethod('before', function(value, element, params) {
  return value && $(params).val() >= value;
},
Translator.trans('validate.before.message')
);
//检查将废除,没有严格的时间转换，有兼容问题
$.validator.addMethod('after', function(value, element, params) {

  return value && $(params).val() < value;
},
Translator.trans('validate.after.message')
);
//检查将废除，存在兼容性问题
$.validator.addMethod('feature', function(value, element, params) {
  return value && (new Date(value).getTime()) > Date.now();
},
Translator.trans('validate.feature.message')
);

$.validator.addMethod('qq', function(value, element) {
  return this.optional(element) || /^[1-9]\d{4,}$/.test(value);
}, Translator.trans('validate.valid_qq_input.message'));

$.validator.addMethod('weixin', function(value, element) {
  return this.optional(element) || /^[-_a-zA-Z0-9]{6,20}$/.test(value);
}, Translator.trans('validate.valid_weixin_input.message'));

$.validator.addMethod('mobile', function(value, element) {
  return this.optional(element) || /^1\d{10}$/.test(value);
}, Translator.trans('validate.valid_mobile_input.message'));

$.validator.addMethod('url', function(value, element) {
  return this.optional(element) || /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/.test(value);
}, Translator.trans('validate.valid_url_input.message'));

$.validator.addMethod('chinese', function(value, element) {
  return this.optional(element) || /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i.test($.trim(value));
}, Translator.trans('validate.valid_chinese_input.message'));

$.validator.addMethod('chinese_limit', function(value, element, params) {
  let l = strlen(value);
  console.log('params', params);
  return this.optional(element) || l <= Number(params);
}, Translator.trans('validate.chinese_limit.message'));

$.validator.addMethod('isImage', function(value, element) {

  if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
    return this.optional(element) || true;
  }

  const imgType = ['jpg', 'JPG', 'jpeg', 'JPEG', 'bmp', 'BMP', 'gif', 'GIF', 'png', 'PNG'];

  // imgType = $(element).attr('accept').replace(/image\//g,"").split(',');

  for (let i = 0; i < imgType.length; i++) {
    if (value.indexOf(imgType[i]) > 0) {
      return this.optional(element) || true;
    }
  }

}, Translator.trans('validate.valid_image_input.message'));

$.validator.addMethod('limitSize', function(value, element) {
  if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
    return this.optional(element) || true;
  }

  const fileSize = $(element)[0]['files'][0].size;

  return this.optional(element) || fileSize / 1024 <= 2048;

}, Translator.trans('validate.limit_size.message'));


jQuery.validator.addMethod('max_year', function(value, element) {
  return this.optional(element) || value < 100000;
}, Translator.trans('validate.max_year.message'));

$.validator.addMethod('feature', function(value, element, params) {
  return value && (new Date(value).getTime()) > Date.now();
},
Translator.trans('validate.feature.message')
);

$.validator.addMethod('next_day', function(value, element, params) {
  let now = new Date();
  let next = new Date(now + 86400 * 1000);
  return value && next <= new Date(value);
},
Translator.trans('validate.next_day.message')
);

$.validator.addMethod('chinese_alphanumeric', function(value, element, params) {
  return this.optional(element) || /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i.test(value);
}, jQuery.validator.format(Translator.trans('validate.chinese_alphanumeric.message')));

$.validator.addMethod('alphanumeric', function(value, element, params) {
  return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value);
}, jQuery.validator.format(Translator.trans('validate.alphanumeric.message')));

$.validator.addMethod('raty_star', function(value, element) {
  return this.optional(element) || /^[1-5]$/.test(value);
}, jQuery.validator.format(Translator.trans('validate.raty_star.message')));

$.validator.addMethod('reg_inviteCode', function(value, element) {
  return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
}, jQuery.validator.format(Translator.trans('validate.reg_invite_code.message')));

$.validator.addMethod('phone', function(value, element) {
  return this.optional(element) || /^1\d{10}$/.test(value);
}, $.validator.format(Translator.trans('validate.phone.message')));

$.validator.addMethod('nickname', function(value, element, params) {
  return this.optional(element) || !/^1\d{10}$/.test(value);
}, Translator.trans('validate.nickname.message'));

$.validator.addMethod('es_remote', function(value, element, params) {
  console.log('es_remote');
  let url = $(element).data('url') ? $(element).data('url') : null;
  let type = params.type ? params.type : 'GET';
  let data = params.data ? params.data : { value: value };
  const finalData = {};
  for (let item in data) {
    const prop = data[item];
    if (typeof prop === 'function') {
      finalData[item] = prop();
    }
  }

  let callback = params.callback ? params.callback : null;
  let isSuccess = 0;
  this.valueCache ? this.valueCache : {};
  let dataValue = isEmpty(finalData) ? data : finalData;
  let cacheKey = url + type + JSON.stringify(dataValue);

  if (cacheKey in this.valueCache) {
    $.validator.messages.es_remote = this.valueCache[cacheKey].message;
    
    let result = this.optional(element) || this.valueCache[cacheKey].isSuccess;
    if (typeof callback === 'function') {
      callback(result);
    }
    return result;
  }

  $.ajax({
    url: url,
    async: false,
    type: type,
    data: data,
    dataType: 'json'
  }).success((response) => {
    this.valueCache[cacheKey] = {};

    if (axis.isObject(response)) {
      isSuccess = response.success;
      $.validator.messages.es_remote = Translator.trans(response.message);
      this.valueCache[cacheKey].message = $.validator.messages.es_remote;

    } else if (axis.isString(response)) {
      isSuccess = false;
      $.validator.messages.es_remote = Translator.trans(response);
      this.valueCache[cacheKey].message = $.validator.messages.es_remote;

    } else if (axis.isBoolean(response)) {
      isSuccess = response;
      this.valueCache[cacheKey].message = Translator.trans('validate.es_remote.message');
    }

    this.valueCache[cacheKey].isSuccess = isSuccess;

    if (typeof callback === 'function') {
      callback(isSuccess);
    }
  });
  
  return this.optional(element) || isSuccess;

}, Translator.trans('validate.es_remote.message'));

$.validator.addMethod('reg_inviteCode', function(value, element) {
  return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
}, Translator.trans('validate.reg_invite_code.message'));

$.validator.addMethod('byte_minlength', function(value, element, params) {
  let l = calculateByteLength(value);
  let bool = l >= Number(params);
  if (!bool) {
    $.validator.messages.byte_minlength = `字符长度必须大于等于${params}，一个中文字算2个字符`;
  }
  return this.optional(element) || bool;
}, Translator.trans('validate.byte_minlength.message'));

$.validator.addMethod('byte_maxlength', function(value, element, params) {
  let l = calculateByteLength(value);
  let bool = l <= Number(params);
  if (!bool) {
    $.validator.messages.byte_maxlength = `字符长度必须小于等于${params}，一个中文字算2个字符`;
  }
  return this.optional(element) || l <= Number(params);
}, Translator.trans('validate.byte_maxlength.message'));

$.validator.addMethod('es_email', function(value, element, params) {
  return this.optional(element) || /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value);
}, Translator.trans('validate.valid_email_input.message'));

$.validator.addMethod('es_score', function (value, element, params) {
  return this.optional(element) || /^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(value);
}, Translator.trans('validate.valid_score_input.message'));

$.validator.addMethod('email_or_mobile_check', function (value, element, params) {
  let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var reg_mobile = /^1\d{10}$/;
  var result = false;
  var isEmail = reg_email.test(value);
  var isMobile = reg_mobile.test(value);
  if (isMobile) {
    $('.email_mobile_msg').removeClass('hidden');
    $('.js-captcha').addClass('hidden');
  } else {
    $('.email_mobile_msg').addClass('hidden');
    $('.js-captcha').removeClass('hidden');
  }
  if (isEmail || isMobile) {
    result = true;
  }
  $.validator.messages.email_or_mobile_check = Translator.trans('validate.mobile_or_email_message');
  return this.optional(element) || result;
}, Translator.trans('validate.email_or_mobile_check.message'));

$.validator.addMethod('ckeditor_maxlength', function (value, element, params) {
  $.validator.messages.ckeditor_maxlength = Translator.trans('validate.character_maxlength', {max: params});

  return value.replace(/<\/?[^>]+(>|$)/g, '').replace(/[\r\n]/g,'').length > params ? false : true;
});

$.validator.addMethod('mobile_or_telephone', function (value, element) {
  var reg_mobile = /^1\d{10}$/;
  let reg_telephone = /^([0-9]{3,4}-)?[0-9]{7,8}$/;
  var result = false;
  var isMobile = reg_mobile.test(value);
  var isTelephone = reg_telephone.test(value);
  if (isTelephone || isMobile) {
    result = true;
  }
  $.validator.messages.mobile_or_telephone = Translator.trans('validate.mobile_or_telephone.message');
  return this.optional(element) || result;
}, $.validator.format(Translator.trans('validate.mobile_or_telephone.message')));

function calculateByteLength(string) {
  let length = string.length;
  for (let i = 0; i < string.length; i++) {
    if (string.charCodeAt(i) > 127)
      length++;
  }
  return length;
}