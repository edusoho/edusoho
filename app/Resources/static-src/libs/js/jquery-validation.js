import 'jquery-validation';
import axis from 'common/axis';

$.validator.setDefaults({
	errorClass: 'form-error-message jq-validate-error',
	errorElement: 'p',
	onkeyup: false,
	ignore: '',
	ajax: false,
	currentDom: null,
	highlight: function (element, errorClass, validClass) {
		let $row = $(element).addClass('form-control-error').closest('.form-group').addClass('has-error');
		$row.find('.help-block').hide();
	},
	unhighlight: function (element, errorClass, validClass) {
		let $row = $(element).removeClass('form-control-error').closest('.form-group');
		$row.removeClass('has-error');
		$row.find('.help-block').show();
	},
	errorPlacement: function (error, element) {
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
	invalidHandler : function(data) {
		console.log(data);
	},
	submitError: function (data) {
		console.log('submitError');
	},
	submitSuccess: function (data) {
		console.log('submitSuccess');
	},
	submitHandler: function (form) {
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
				settings.submitSuccess(data);
			}).error((data) => {
				settings.submitError(data);
			});
			$btn.button('reset');
		} else {
			form.submit();
		}
	}
});

$.extend($.validator.prototype, {
	defaultMessage: function (element, rule) {
		if (typeof rule === "string") {
			rule = { method: rule };
		}

		var message = this.findDefined(
			this.customMessage(element.name, rule.method),
			this.customDataMessage(element, rule.method),

			// 'title' is never undefined, so handle empty string as undefined
			!this.settings.ignoreTitle && element.title || undefined,
			$.validator.messages[rule.method],
			"<strong>Warning: No message defined for " + element.name + "</strong>"
		),
			theregex = /\$?\{(\d+)\}/g,
			displayregex = /%display%/g;
		if (typeof message === "function") {
			message = message.call(this, rule.parameters, element);
		} else if (theregex.test(message)) {
			message = $.validator.format(message.replace(theregex, "{$1}"), rule.parameters);
		}

		if (displayregex.test(message)) {
			var labeltext, name;
			var id = $(element).attr("id");
			if (id) {
				labeltext = $("label[for=" + id + "]").text();
				if (labeltext) {
					labeltext = labeltext.replace(/^[\*\s\:\：]*/, "").replace(/[\*\s\:\：]*$/, "");
				}
			}

			name = $(element).data('display') || $(element).attr("name");
			message = message.replace(displayregex, labeltext || name)
		}

		return message;
	}

});

$.extend($.validator.messages, {
	required: "请输入%display%",
	remote: "请修正此字段",
	email: "请输入有效的电子邮件地址",
	url: "请输入有效的网址",
	date: "请输入有效的日期",
	dateISO: "请输入有效的日期 (YYYY-MM-DD)",
	number: "请输入有效的数字",
	digits: "只能输入整数",
	creditcard: "请输入有效的信用卡号码",
	equalTo: "你的输入不相同",
	extension: "请输入有效的后缀",
	maxlength: $.validator.format("最多只能输入 {0} 个字符"),
	minlength: $.validator.format("最少需要输入 {0} 个字符"),
	rangelength: $.validator.format("请输入长度在 {0} 到 {1} 之间的字符串"),
	range: $.validator.format("请输入范围在 {0} 到 {1} 之间的数值"),
	max: $.validator.format("请输入不大于 {0} 的数值"),
	min: $.validator.format("请输入不小于 {0} 的数值")
});

$.validator.addMethod("DateAndTime", function (value, element) {
	let reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
	return this.optional(element) || reg.test(value);
}, $.validator.format("请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm"));

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

$.validator.addMethod("trim", function (value, element, params) {
    return this.optional(element) || $.trim(value).length > 0;
}, Translator.trans("请输入%display%"));

$.validator.addMethod("visible_character", function (value, element, params) {
    return this.optional(element) || (value.match(/\S/g).length === value.length);
}, Translator.trans("不允许输入不可见字符，如空格等"));

$.validator.addMethod("idcardNumber", function (value, element, params) {
	let _check = function (idcardNumber) {
		let reg = /^\d{17}[0-9xX]$/i;
		if (!reg.test(idcardNumber)) {
			return false;
		}
		let n = new Date();
		let y = n.getFullYear();
		if (parseInt(idcardNumber.substr(6, 4)) < 1900 || parseInt(idcardNumber.substr(6, 4)) > y) {
			return false;
		}
		let birth = idcardNumber.substr(6, 4) + "-" + idcardNumber.substr(10, 2) + "-" + idcardNumber.substr(12, 2);
		if (!'undefined' == typeof birth.getDate) {
			return false;
		}
		let IW = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
		let iSum = 0;
		for (let i = 0; i < 17; i++) {
			iSum += parseInt(idcardNumber.charAt(i)) * IW[i];
		}
		let iJYM = iSum % 11;
		let sJYM = ''
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
	}
	return this.optional(element) || _check(value);
}, "请正确输入您的身份证号码");

$.validator.addMethod('positive_integer', function (value, element, params = true) {
	if (!params) {
		return true;
	}
	return this.optional(element) || /^\+?[1-9][0-9]*$/.test(value);
}, Translator.trans("请输入正整数"));


$.validator.addMethod('unsigned_integer', function (value, element) {
	return this.optional(element) || /^\+?[0-9][0-9]*$/.test(value);
}, Translator.trans("请输入非负整数"));

// jQuery.validator.addMethod("unsigned_integer", function (value, element) {
//   return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
// }, "时长必须为非负整数");

jQuery.validator.addMethod("second_range", function (value, element) {
	return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, "请输入0-59之间的数字");

$.validator.addMethod("course_title", function (value, element, params) {
	return this.optional(element) || /^[^<>]*$/.test(value);
}, Translator.trans('不支持输入<、>字符'));

$.validator.addMethod('float', function (value, element) {
	return this.optional(element) || /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i.test(value);
}, Translator.trans("请输入正确的小数,只保留到两位小数"));

$.validator.addMethod('date', function (value, element) {
	return this.optional(element) || /^\d{4}\-[01]?\d\-[0-3]?\d$|^[01]\d\/[0-3]\d\/\d{4}$|^\d{4}年[01]?\d月[0-3]?\d[日号]$/.test(value);
}, Translator.trans("请输入正确的日期"));

$.validator.addMethod("open_live_course_title", function (value, element, params) {
	return this.optional(element) || /^[^<|>|'|"|&|‘|’|”|“]*$/.test(value);
}, Translator.trans('不支持输入<、>、\"、&、‘、’、”、“字符'));

$.validator.addMethod("currency", function (value, element, params) {
	return this.optional(element) || /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
}, Translator.trans('请输入有效价格，最多两位小数，整数位不超过8位！'));

//@TODO这里不应该判断大于0，应该用组合positive_currency:true，min:1，看到替换
$.validator.addMethod("positive_currency", function (value, element, params) {
	return value > 0 && /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
}, Translator.trans('请输入大于0的有效价格，最多两位小数，整数位不超过8位！'));

jQuery.validator.addMethod("max_year", function (value, element) {
	return this.optional(element) || value < 100000;
}, "有效期最大值不能超过99,999天");

$.validator.addMethod("before_date", function (value, element, params) {
	let date = new Date(value);
	let afterDate = new Date($(params).val());
	return this.optional(element) || afterDate >= date;
},
	Translator.trans('开始日期应早于结束日期')
);

$.validator.addMethod("after_date", function (value, element, params) {
	let date = new Date(value);
	let afterDate = new Date($(params).val());
	return this.optional(element) || afterDate <= date;
},
	Translator.trans('开始日期应早于结束日期')
);

$.validator.addMethod("after_now", function (value, element, params) {
	let afterDate = new Date(value.replace(/-/g, '/'));//fix sf;
	return this.optional(element) || afterDate >= new Date();
},
	Translator.trans('开始时间应晚于当前时间')
);

//日期比较，不进行时间比较
$.validator.addMethod("after_now_date", function (value, element, params) {
	let now = new Date();
	let afterDate = new Date(value);
	let str = now.getFullYear() + "/" + (now.getMonth() + 1) + "/" + now.getDate();
	return this.optional(element) || afterDate >= new Date(str);
},
	Translator.trans('开始日期应晚于当前日期')
);

//检查将废除,没有严格的时间转换，有兼容问题
$.validator.addMethod("before", function (value, element, params) {
	return value && $(params).val() >= value;
},
	Translator.trans('开始日期应早于结束日期')
);
//检查将废除,没有严格的时间转换，有兼容问题
$.validator.addMethod("after", function (value, element, params) {

	return value && $(params).val() < value;
},
	Translator.trans('结束日期应晚于开始日期')
);
//检查将废除，存在兼容性问题
$.validator.addMethod("feature", function (value, element, params) {
	return value && (new Date(value).getTime()) > Date.now();
},
	Translator.trans('购买截止时间需在当前时间之后')
);

$.validator.addMethod('qq', function (value, element) {
	return this.optional(element) || /^[1-9]\d{4,}$/.test(value);
}, Translator.trans('请输入正确的QQ号'));

$.validator.addMethod('mobile', function (value, element) {
	return this.optional(element) || /^1\d{10}$/.test(value);
}, Translator.trans('请输入正确的手机号'));

$.validator.addMethod('url', function (value, element) {
	return this.optional(element) || /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/.test(value)
}, Translator.trans('地址不正确，须以http://或者https://开头。'));

$.validator.addMethod('chinese', function (value, element) {
	return this.optional(element) || /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i.test(value);
}, Translator.trans('必须是中文字'));

$.validator.addMethod('chinese_limit', function (value, element, params) {
	let l = strlen(value);
	console.log('params', params)
	return this.optional(element) || l <= Number(params);
}, Translator.trans('长度必须小于等于 {0} 字符,一个中文为2个字符'));

$.validator.addMethod('isImage', function (value, element) {

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

}, Translator.trans('只能上传图片'));

$.validator.addMethod('limitSize', function (value, element) {
	if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
		return this.optional(element) || true;
	}

	const fileSize = $(element)[0]['files'][0].size;

	return this.optional(element) || fileSize / 1024 <= 2048;

}, Translator.trans('大小不能超过2M'));


jQuery.validator.addMethod("max_year", function (value, element) {
	return this.optional(element) || value < 100000;
}, "有效期最大值不能超过99,999天");

$.validator.addMethod("feature", function (value, element, params) {
	return value && (new Date(value).getTime()) > Date.now();
},
	Translator.trans('购买截止时间需在当前时间之后')
);

$.validator.addMethod("next_day", function (value, element, params) {
	let now = new Date();
	let next = new Date(now + 86400 * 1000);
	return value && next <= new Date(value);
},
	Translator.trans('开始时间应晚于当前时间')
);

$.validator.addMethod("chinese_alphanumeric", function (value, element, params) {
	return this.optional(element) || /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i.test(value)
}, jQuery.validator.format('只支持中文字、英文字母、数字及_ . ·'));

$.validator.addMethod("alphanumeric", function (value, element, params) {
	return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value)
}, jQuery.validator.format('必须是英文字母、数字及下划线组成'));

$.validator.addMethod('raty_star', function (value, element) {
	return this.optional(element) || /^[1-5]$/.test(value);
}, Translator.trans('请打分'));

$.validator.addMethod('reg_inviteCode', function (value, element) {
	return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
}, Translator.trans('必须是5位数字、英文字母组成'));

$.validator.addMethod('phone', function (value, element) {
	return this.optional(element) || /^1\d{10}$/.test(value);
}, $.validator.format("请输入有效手机号码(仅仅支持中国大陆手机号码)"));

$.validator.addMethod("nickname", function (value, element, params) {
	return this.optional(element) || !/^1\d{10}$/.test(value)
}, Translator.trans('不允许以1开头的11位纯数字'));

//@TODO 确认用es_remote代替
$.validator.addMethod('passwordCheck', function (value, element) {
	let url = $(element).data('url') ? $(element).data('url') : null;
	let type = $(element).data('type') ? $(element).data('type') : 'POST';
	let isSuccess = 0;
	$.ajax({
		url: url,
		type: type,
		async: false,
		data: { value: value },
		dataType: 'json'
	})
		.success(function (response) {
			isSuccess = response.success;
		})
	return this.optional(element) || isSuccess
}, Translator.trans('密码错误'))

//@TODO 确认用es_remote代替
$.validator.addMethod('smsCode', function (value, element) {
	let url = $(element).data('url');
	let isSuccess = 0;
	$.ajax({
		url: url,
		type: 'get',
		async: false,
		data: { value: $(element).val() },
		dataType: 'json'
	})
		.success(function (response) {
			isSuccess = response.success;
		})
	return this.optional(element) || isSuccess
}, Translator.trans('验证码错误'));

$.validator.addMethod('es_remote', function (value, element, params) {
	let $element = $(element);
	let url = $(element).data('url') ? $(element).data('url') : null;
	let type = params.type ? params.type : 'GET';
	let data = params.data ? params.data : { value: value };
	let callback = params.callback ? params.callback : null;
	let isSuccess = 0;
	$.ajax({
		url: url,
		async: false,
		type: type,
		data: data,
		dataType: 'json'
	})
	.success((response) => {
		console.log('remote');
		if (axis.isObject(response)) {
			isSuccess = response.success;
			$.validator.messages.es_remote = response.message;

		} else if (axis.isString(response)) {
			isSuccess = false;
			$.validator.messages.es_remote = response;
		} else if (axis.isBoolean(response)) {
			isSuccess = response;
		}
		if (callback) {
			callback(isSuccess);
		}
	})
	return this.optional(element) || isSuccess;
}, Translator.trans('验证错误'));

$.validator.addMethod('reg_inviteCode', function (value, element) {
	return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
}, Translator.trans('必须是5位数字、英文字母组成'));

$.validator.addMethod('byte_minlength', function (value, element, params) {
	let l = calculateByteLength(value);
	let bool = l >= Number(params);
	if (!bool) {
		$.validator.messages.byte_minlength = `字符长度必须大于等于${params}，一个中文字算2个字符`;
	}
	return this.optional(element) || bool;
}, Translator.trans('字符长度必须大于等于%min%，一个中文字算2个字符'));

$.validator.addMethod('byte_maxlength', function (value, element, params) {
	let l = calculateByteLength(value);
	let bool = l <= Number(params);
	if (!bool) {
		$.validator.messages.byte_maxlength = `字符长度必须小于等于${params}，一个中文字算2个字符`;
	}
	return this.optional(element) || l <= Number(params);
}, Translator.trans('字符长度必须小于等于%max%，一个中文字算2个字符'));

$.validator.addMethod('es_email', function (value, element, params) {
	return this.optional(element) || /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value);
}, Translator.trans('请输入正确格式的邮箱'));

function calculateByteLength(string) {
	let length = string.length;
	for (let i = 0; i < string.length; i++) {
		if (string.charCodeAt(i) > 127)
			length++;
	}
	return length;
}
