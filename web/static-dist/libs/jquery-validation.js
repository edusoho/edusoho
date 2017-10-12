/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/static-dist/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "759dade98296e2e843ed":
/***/ (function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;'use strict';
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	(function (root, factory) {
	  if (true) {
	    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory), __WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ? (__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	  } else if ((typeof exports === 'undefined' ? 'undefined' : _typeof(exports)) === 'object') {
	    module.exports = factory();
	  } else {
	    root.axis = factory();
	  }
	})(undefined, function () {
	  'use strict';
	
	  var axis = {};
	  var types = 'Array Object String Date RegExp Function Boolean Number Null Undefined'.split(' ');
	  function type() {
	    return Object.prototype.toString.call(this).slice(8, -1);
	  }
	  for (var i = types.length; i--;) {
	    axis['is' + types[i]] = function (self) {
	      return function (elem) {
	        return type.call(elem) === self;
	      };
	    }(types[i]);
	  }
	  return axis;
	});
	// axis.isArray([]); 
	// axis.isObject({}); 
	// axis.isString('');
	// axis.isDate(new Date()); 
	// axis.isRegExp(/test/i); 
	// axis.isFunction(function () {});
	// axis.isBoolean(true); 
	// axis.isNumber(1); 
	// axis.isNull(null); 
	// axis.isUndefined();

/***/ }),

/***/ "09902a336c15906c385b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	__webpack_require__("94710a60abf48fcc23c3");
	
	var _axis = __webpack_require__("759dade98296e2e843ed");
	
	var _axis2 = _interopRequireDefault(_axis);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$.validator.setDefaults({
		errorClass: 'form-error-message jq-validate-error',
		errorElement: 'p',
		onkeyup: false,
		ignore: '',
		ajax: false,
		currentDom: null,
		highlight: function highlight(element, errorClass, validClass) {
			var $row = $(element).addClass('form-control-error').closest('.form-group').addClass('has-error');
			$row.find('.help-block').hide();
		},
		unhighlight: function unhighlight(element, errorClass, validClass) {
			var $row = $(element).removeClass('form-control-error').closest('.form-group');
			$row.removeClass('has-error');
			$row.find('.help-block').show();
		},
		errorPlacement: function errorPlacement(error, element) {
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
		invalidHandler: function invalidHandler(data) {
			console.log(data);
		},
		submitError: function submitError(data) {
			console.log('submitError');
		},
		submitSuccess: function submitSuccess(data) {
			console.log('submitSuccess');
		},
		submitHandler: function submitHandler(form) {
			console.log('submitHandler');
			//规定不要用模态框 submit按钮（<input type=’submit’>）提交表单；
			var $form = $(form);
			var settings = this.settings;
			var $btn = $(settings.currentDom);
			if (!$btn.length) {
				$btn = $(form).find('[type="submit"]');
			}
			$btn.button('loading');
			if (settings.ajax) {
				$.post($form.attr('action'), $form.serializeArray(), function (data) {
					$btn.button('reset');
					settings.submitSuccess(data);
				}).error(function (data) {
					$btn.button('reset');
					settings.submitError(data);
				});
			} else {
				form.submit();
			}
		}
	});
	
	$.extend($.validator.prototype, {
		defaultMessage: function defaultMessage(element, rule) {
			if (typeof rule === "string") {
				rule = { method: rule };
			}
	
			var message = this.findDefined(this.customMessage(element.name, rule.method), this.customDataMessage(element, rule.method),
	
			// 'title' is never undefined, so handle empty string as undefined
			!this.settings.ignoreTitle && element.title || undefined, $.validator.messages[rule.method], "<strong>Warning: No message defined for " + element.name + "</strong>"),
			    theregex = /\$?\{(\d+)\}/g,
			    displayregex = /%display%/g;
			if (typeof message === "function") {
				message = message.call(this, rule.parameters, element);
			} else if (theregex.test(message)) {
				message = $.validator.format(message.replace(theregex, "{$1}"), rule.parameters);
			}
	
			if (displayregex.test(message)) {
				var labeltext, name;
				var id = $(element).attr("id") || $(element).attr("name");
				if (id) {
					labeltext = $("label[for=" + id + "]").text();
					if (labeltext) {
						labeltext = labeltext.replace(/^[\*\s\:\：]*/, "").replace(/[\*\s\:\：]*$/, "");
					}
				}
	
				name = $(element).data('display') || $(element).attr("name");
				message = message.replace(displayregex, labeltext || name);
			}
	
			return message;
		}
	
	});
	
	$.extend($.validator.messages, {
		required: Translator.trans('validate.required.message'),
		remote: "请修正此字段",
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
	
	$.validator.addMethod("DateAndTime", function (value, element) {
		var reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
		return this.optional(element) || reg.test(value);
	}, $.validator.format(Translator.trans('validate.valid_date_and_time_input.message')));
	
	function strlen(str) {
		var len = 0;
		for (var i = 0; i < str.length; i++) {
			var chars = str.charCodeAt(i);
			//单字节加1
			if (chars >= 0x0001 && chars <= 0x007e || 0xff60 <= chars && chars <= 0xff9f) {
				len++;
			} else {
				len += 2;
			}
		}
		return len;
	}
	
	$.validator.addMethod("trim", function (value, element, params) {
		return this.optional(element) || $.trim(value).length > 0;
	}, Translator.trans('validate.trim.message'));
	
	$.validator.addMethod("visible_character", function (value, element, params) {
		return this.optional(element) || value.match(/\S/g).length === value.length;
	}, Translator.trans('validate.visible_character.message'));
	
	$.validator.addMethod("idcardNumber", function (value, element, params) {
		var _check = function _check(idcardNumber) {
			var reg = /^\d{17}[0-9xX]$/i;
			if (!reg.test(idcardNumber)) {
				return false;
			}
			var n = new Date();
			var y = n.getFullYear();
			if (parseInt(idcardNumber.substr(6, 4)) < 1900 || parseInt(idcardNumber.substr(6, 4)) > y) {
				return false;
			}
			var birth = idcardNumber.substr(6, 4) + "-" + idcardNumber.substr(10, 2) + "-" + idcardNumber.substr(12, 2);
			if (!'undefined' == _typeof(birth.getDate)) {
				return false;
			}
			var IW = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
			var iSum = 0;
			for (var i = 0; i < 17; i++) {
				iSum += parseInt(idcardNumber.charAt(i)) * IW[i];
			}
			var iJYM = iSum % 11;
			var sJYM = '';
			if (iJYM == 0) sJYM = '1';else if (iJYM == 1) sJYM = '0';else if (iJYM == 2) sJYM = 'x';else if (iJYM == 3) sJYM = '9';else if (iJYM == 4) sJYM = '8';else if (iJYM == 5) sJYM = '7';else if (iJYM == 6) sJYM = '6';else if (iJYM == 7) sJYM = '5';else if (iJYM == 8) sJYM = '4';else if (iJYM == 9) sJYM = '3';else if (iJYM == 10) sJYM = '2';
			var cCheck = idcardNumber.charAt(17).toLowerCase();
			if (cCheck != sJYM) {
				return false;
			}
			return true;
		};
		return this.optional(element) || _check(value);
	}, Translator.trans('validate.idcard_number_input.message'));
	
	$.validator.addMethod("visible_character", function (value, element, params) {
		return this.optional(element) || $.trim(value).length > 0;
	}, Translator.trans('validate.visible_character_input.message'));
	
	$.validator.addMethod('positive_integer', function (value, element) {
		var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	
		if (!params) {
			return true;
		}
		return this.optional(element) || /^\+?[1-9][0-9]*$/.test(value);
	}, Translator.trans('validate.positive_integer.message'));
	
	$.validator.addMethod('unsigned_integer', function (value, element) {
		return this.optional(element) || /^\+?[0-9][0-9]*$/.test(value);
	}, Translator.trans('validate.unsigned_integer.message'));
	
	// jQuery.validator.addMethod("unsigned_integer", function (value, element) {
	//   return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
	// }, "时长必须为非负整数");
	
	jQuery.validator.addMethod("second_range", function (value, element) {
		return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
	}, Translator.trans('validate.second_range.message'));
	
	$.validator.addMethod("course_title", function (value, element, params) {
		return this.optional(element) || /^[^<>]*$/.test(value);
	}, Translator.trans('validate.course_title.message'));
	
	$.validator.addMethod('float', function (value, element) {
		return this.optional(element) || /^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i.test(value);
	}, Translator.trans('validate.float_input.message'));
	
	$.validator.addMethod('date', function (value, element) {
		return this.optional(element) || /^\d{4}\-[01]?\d\-[0-3]?\d$|^[01]\d\/[0-3]\d\/\d{4}$|^\d{4}年[01]?\d月[0-3]?\d[日号]$/.test(value);
	}, Translator.trans('validate.valid_date_input.message'));
	
	$.validator.addMethod("open_live_course_title", function (value, element, params) {
		return this.optional(element) || /^[^<|>|'|"|&|‘|’|”|“]*$/.test(value);
	}, Translator.trans('validate.open_live_course_title.message'));
	
	$.validator.addMethod("currency", function (value, element, params) {
		return this.optional(element) || /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
	}, Translator.trans('validate.currency.message'));
	
	//@TODO这里不应该判断大于0，应该用组合positive_currency:true，min:1，看到替换
	$.validator.addMethod("positive_currency", function (value, element, params) {
		return value > 0 && /^[0-9]{0,8}(\.\d{0,2})?$/.test(value);
	}, Translator.trans('validate.positive_currency.message'));
	
	jQuery.validator.addMethod("max_year", function (value, element) {
		return this.optional(element) || value < 100000;
	}, Translator.trans('validate.max_year.message'));
	
	$.validator.addMethod("before_date", function (value, element, params) {
		var date = new Date(value);
		var afterDate = new Date($(params).val());
		return this.optional(element) || afterDate >= date;
	}, Translator.trans('validate.before_date.message'));
	
	$.validator.addMethod("after_date", function (value, element, params) {
		var date = new Date(value);
		var afterDate = new Date($(params).val());
		return this.optional(element) || afterDate <= date;
	}, Translator.trans('validate.after_date.message'));
	
	$.validator.addMethod("after_now", function (value, element, params) {
		var afterDate = new Date(value.replace(/-/g, '/')); //fix sf;
		return this.optional(element) || afterDate >= new Date();
	}, Translator.trans('validate.after_now.message'));
	
	//日期比较，不进行时间比较
	$.validator.addMethod("after_now_date", function (value, element, params) {
		var now = new Date();
		var afterDate = new Date(value);
		var str = now.getFullYear() + "/" + (now.getMonth() + 1) + "/" + now.getDate();
		return this.optional(element) || afterDate >= new Date(str);
	}, Translator.trans('validate.after_now_date.message'));
	
	//检查将废除,没有严格的时间转换，有兼容问题
	$.validator.addMethod("before", function (value, element, params) {
		return value && $(params).val() >= value;
	}, Translator.trans('validate.before.message'));
	//检查将废除,没有严格的时间转换，有兼容问题
	$.validator.addMethod("after", function (value, element, params) {
	
		return value && $(params).val() < value;
	}, Translator.trans('validate.after.message'));
	//检查将废除，存在兼容性问题
	$.validator.addMethod("feature", function (value, element, params) {
		return value && new Date(value).getTime() > Date.now();
	}, Translator.trans('validate.feature.message'));
	
	$.validator.addMethod('qq', function (value, element) {
		return this.optional(element) || /^[1-9]\d{4,}$/.test(value);
	}, Translator.trans('validate.valid_qq_input.message'));
	
	$.validator.addMethod('mobile', function (value, element) {
		return this.optional(element) || /^1\d{10}$/.test(value);
	}, Translator.trans('validate.valid_mobile_input.message'));
	
	$.validator.addMethod('url', function (value, element) {
		return this.optional(element) || /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/.test(value);
	}, Translator.trans('validate.valid_url_input.message'));
	
	$.validator.addMethod('chinese', function (value, element) {
		return this.optional(element) || /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i.test(value);
	}, Translator.trans('validate.valid_chinese_input.message'));
	
	$.validator.addMethod('chinese_limit', function (value, element, params) {
		var l = strlen(value);
		console.log('params', params);
		return this.optional(element) || l <= Number(params);
	}, Translator.trans('validate.chinese_limit.message'));
	
	$.validator.addMethod('isImage', function (value, element) {
	
		if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
			return this.optional(element) || true;
		}
	
		var imgType = ['jpg', 'JPG', 'jpeg', 'JPEG', 'bmp', 'BMP', 'gif', 'GIF', 'png', 'PNG'];
	
		// imgType = $(element).attr('accept').replace(/image\//g,"").split(',');
	
		for (var i = 0; i < imgType.length; i++) {
			if (value.indexOf(imgType[i]) > 0) {
				return this.optional(element) || true;
			}
		}
	}, Translator.trans('validate.valid_image_input.message'));
	
	$.validator.addMethod('limitSize', function (value, element) {
		if (navigator.userAgent.toLowerCase().indexOf('msie') > 0) {
			return this.optional(element) || true;
		}
	
		var fileSize = $(element)[0]['files'][0].size;
	
		return this.optional(element) || fileSize / 1024 <= 2048;
	}, Translator.trans('validate.limit_size.message'));
	
	jQuery.validator.addMethod("max_year", function (value, element) {
		return this.optional(element) || value < 100000;
	}, Translator.trans('validate.max_year.message'));
	
	$.validator.addMethod("feature", function (value, element, params) {
		return value && new Date(value).getTime() > Date.now();
	}, Translator.trans('validate.feature.message'));
	
	$.validator.addMethod("next_day", function (value, element, params) {
		var now = new Date();
		var next = new Date(now + 86400 * 1000);
		return value && next <= new Date(value);
	}, Translator.trans('validate.next_day.message'));
	
	$.validator.addMethod("chinese_alphanumeric", function (value, element, params) {
		return this.optional(element) || /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i.test(value);
	}, jQuery.validator.format(Translator.trans('validate.chinese_alphanumeric.message')));
	
	$.validator.addMethod("alphanumeric", function (value, element, params) {
		return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value);
	}, jQuery.validator.format(Translator.trans('validate.alphanumeric.message')));
	
	$.validator.addMethod('raty_star', function (value, element) {
		return this.optional(element) || /^[1-5]$/.test(value);
	}, jQuery.validator.format(Translator.trans('validate.raty_star.message')));
	
	$.validator.addMethod('reg_inviteCode', function (value, element) {
		return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
	}, jQuery.validator.format(Translator.trans('validate.reg_invite_code.message')));
	
	$.validator.addMethod('phone', function (value, element) {
		return this.optional(element) || /^1\d{10}$/.test(value);
	}, $.validator.format(Translator.trans('validate.phone.message')));
	
	$.validator.addMethod("nickname", function (value, element, params) {
		return this.optional(element) || !/^1\d{10}$/.test(value);
	}, Translator.trans('validate.nickname.message'));
	
	//@TODO 确认用es_remote代替
	$.validator.addMethod('passwordCheck', function (value, element) {
		var url = $(element).data('url') ? $(element).data('url') : null;
		var type = $(element).data('type') ? $(element).data('type') : 'POST';
		var isSuccess = 0;
		$.ajax({
			url: url,
			type: type,
			async: false,
			data: { value: value },
			dataType: 'json'
		}).success(function (response) {
			isSuccess = response.success;
		});
		return this.optional(element) || isSuccess;
	}, Translator.trans('validate.password_check.message')
	
	//@TODO 确认用es_remote代替
	);$.validator.addMethod('smsCode', function (value, element) {
		var url = $(element).data('url');
		var isSuccess = 0;
		$.ajax({
			url: url,
			type: 'get',
			async: false,
			data: { value: $(element).val() },
			dataType: 'json'
		}).success(function (response) {
			isSuccess = response.success;
		});
		return this.optional(element) || isSuccess;
	}, Translator.trans('validate.sms_code.message'));
	
	$.validator.addMethod('es_remote', function (value, element, params) {
		console.log('es_remotees_remote');
		var $element = $(element);
		var url = $(element).data('url') ? $(element).data('url') : null;
		var type = params.type ? params.type : 'GET';
		var data = params.data ? params.data : { value: value };
		var callback = params.callback ? params.callback : null;
		var isSuccess = 0;
		$.ajax({
			url: url,
			async: false,
			type: type,
			data: data,
			dataType: 'json'
		}).success(function (response) {
			console.log('remote');
			if (_axis2["default"].isObject(response)) {
				isSuccess = response.success;
				$.validator.messages.es_remote = Translator.trans(response.message);
			} else if (_axis2["default"].isString(response)) {
				isSuccess = false;
				$.validator.messages.es_remote = Translator.trans(response);
			} else if (_axis2["default"].isBoolean(response)) {
				isSuccess = response;
			}
			if (callback) {
				callback(isSuccess);
			}
		});
		return this.optional(element) || isSuccess;
	}, Translator.trans('validate.es_remote.message'));
	
	$.validator.addMethod('reg_inviteCode', function (value, element) {
		return this.optional(element) || /^[a-z0-9A-Z]{5}$/.test(value);
	}, Translator.trans('validate.reg_invite_code.message'));
	
	$.validator.addMethod('byte_minlength', function (value, element, params) {
		var l = calculateByteLength(value);
		var bool = l >= Number(params);
		if (!bool) {
			$.validator.messages.byte_minlength = '\u5B57\u7B26\u957F\u5EA6\u5FC5\u987B\u5927\u4E8E\u7B49\u4E8E' + params + '\uFF0C\u4E00\u4E2A\u4E2D\u6587\u5B57\u7B972\u4E2A\u5B57\u7B26';
		}
		return this.optional(element) || bool;
	}, Translator.trans('validate.byte_minlength.message'));
	
	$.validator.addMethod('byte_maxlength', function (value, element, params) {
		var l = calculateByteLength(value);
		var bool = l <= Number(params);
		if (!bool) {
			$.validator.messages.byte_maxlength = '\u5B57\u7B26\u957F\u5EA6\u5FC5\u987B\u5C0F\u4E8E\u7B49\u4E8E' + params + '\uFF0C\u4E00\u4E2A\u4E2D\u6587\u5B57\u7B972\u4E2A\u5B57\u7B26';
		}
		return this.optional(element) || l <= Number(params);
	}, Translator.trans('validate.byte_maxlength.message'));
	
	$.validator.addMethod('es_email', function (value, element, params) {
		return this.optional(element) || /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value);
	}, Translator.trans('validate.valid_email_input.message'));
	
	function calculateByteLength(string) {
		var length = string.length;
		for (var i = 0; i < string.length; i++) {
			if (string.charCodeAt(i) > 127) length++;
		}
		return length;
	}

/***/ }),

/***/ "94710a60abf48fcc23c3":
/***/ (function(module, exports, __webpack_require__) {

	/*** IMPORTS FROM imports-loader ***/
	var define = false;
	var module = false;
	var exports = false;
	(function() {
	
	/*!
	 * jQuery Validation Plugin v1.15.1
	 *
	 * http://jqueryvalidation.org/
	 *
	 * Copyright (c) 2016 Jörn Zaefferer
	 * Released under the MIT license
	 */
	(function( factory ) {
		if ( typeof define === "function" && define.amd ) {
			define( ["jquery"], factory );
		} else if (typeof module === "object" && module.exports) {
			module.exports = factory( __webpack_require__( 1 ) );
		} else {
			factory( jQuery );
		}
	}(function( $ ) {
	
	$.extend( $.fn, {
	
		// http://jqueryvalidation.org/validate/
		validate: function( options ) {
	
			// If nothing is selected, return nothing; can't chain anyway
			if ( !this.length ) {
				if ( options && options.debug && window.console ) {
					console.warn( "Nothing selected, can't validate, returning nothing." );
				}
				return;
			}
	
			// Check if a validator for this form was already created
			var validator = $.data( this[ 0 ], "validator" );
			if ( validator ) {
				return validator;
			}
	
			// Add novalidate tag if HTML5.
			this.attr( "novalidate", "novalidate" );
	
			validator = new $.validator( options, this[ 0 ] );
			$.data( this[ 0 ], "validator", validator );
	
			if ( validator.settings.onsubmit ) {
	
				this.on( "click.validate", ":submit", function( event ) {
					if ( validator.settings.submitHandler ) {
						validator.submitButton = event.target;
					}
	
					// Allow suppressing validation by adding a cancel class to the submit button
					if ( $( this ).hasClass( "cancel" ) ) {
						validator.cancelSubmit = true;
					}
	
					// Allow suppressing validation by adding the html5 formnovalidate attribute to the submit button
					if ( $( this ).attr( "formnovalidate" ) !== undefined ) {
						validator.cancelSubmit = true;
					}
				} );
	
				// Validate the form on submit
				this.on( "submit.validate", function( event ) {
					if ( validator.settings.debug ) {
	
						// Prevent form submit to be able to see console output
						event.preventDefault();
					}
					function handle() {
						var hidden, result;
						if ( validator.settings.submitHandler ) {
							if ( validator.submitButton ) {
	
								// Insert a hidden input as a replacement for the missing submit button
								hidden = $( "<input type='hidden'/>" )
									.attr( "name", validator.submitButton.name )
									.val( $( validator.submitButton ).val() )
									.appendTo( validator.currentForm );
							}
							result = validator.settings.submitHandler.call( validator, validator.currentForm, event );
							if ( validator.submitButton ) {
	
								// And clean up afterwards; thanks to no-block-scope, hidden can be referenced
								hidden.remove();
							}
							if ( result !== undefined ) {
								return result;
							}
							return false;
						}
						return true;
					}
	
					// Prevent submit for invalid forms or custom submit handlers
					if ( validator.cancelSubmit ) {
						validator.cancelSubmit = false;
						return handle();
					}
					if ( validator.form() ) {
						if ( validator.pendingRequest ) {
							validator.formSubmitted = true;
							return false;
						}
						return handle();
					} else {
						validator.focusInvalid();
						return false;
					}
				} );
			}
	
			return validator;
		},
	
		// http://jqueryvalidation.org/valid/
		valid: function() {
			var valid, validator, errorList;
	
			if ( $( this[ 0 ] ).is( "form" ) ) {
				valid = this.validate().form();
			} else {
				errorList = [];
				valid = true;
				validator = $( this[ 0 ].form ).validate();
				this.each( function() {
					valid = validator.element( this ) && valid;
					if ( !valid ) {
						errorList = errorList.concat( validator.errorList );
					}
				} );
				validator.errorList = errorList;
			}
			return valid;
		},
	
		// http://jqueryvalidation.org/rules/
		rules: function( command, argument ) {
			var element = this[ 0 ],
				settings, staticRules, existingRules, data, param, filtered;
	
			// If nothing is selected, return empty object; can't chain anyway
			if ( element == null || element.form == null ) {
				return;
			}
	
			if ( command ) {
				settings = $.data( element.form, "validator" ).settings;
				staticRules = settings.rules;
				existingRules = $.validator.staticRules( element );
				switch ( command ) {
				case "add":
					$.extend( existingRules, $.validator.normalizeRule( argument ) );
	
					// Remove messages from rules, but allow them to be set separately
					delete existingRules.messages;
					staticRules[ element.name ] = existingRules;
					if ( argument.messages ) {
						settings.messages[ element.name ] = $.extend( settings.messages[ element.name ], argument.messages );
					}
					break;
				case "remove":
					if ( !argument ) {
						delete staticRules[ element.name ];
						return existingRules;
					}
					filtered = {};
					$.each( argument.split( /\s/ ), function( index, method ) {
						filtered[ method ] = existingRules[ method ];
						delete existingRules[ method ];
						if ( method === "required" ) {
							$( element ).removeAttr( "aria-required" );
						}
					} );
					return filtered;
				}
			}
	
			data = $.validator.normalizeRules(
			$.extend(
				{},
				$.validator.classRules( element ),
				$.validator.attributeRules( element ),
				$.validator.dataRules( element ),
				$.validator.staticRules( element )
			), element );
	
			// Make sure required is at front
			if ( data.required ) {
				param = data.required;
				delete data.required;
				data = $.extend( { required: param }, data );
				$( element ).attr( "aria-required", "true" );
			}
	
			// Make sure remote is at back
			if ( data.remote ) {
				param = data.remote;
				delete data.remote;
				data = $.extend( data, { remote: param } );
			}
	
			return data;
		}
	} );
	
	// Custom selectors
	$.extend( $.expr[ ":" ], {
	
		// http://jqueryvalidation.org/blank-selector/
		blank: function( a ) {
			return !$.trim( "" + $( a ).val() );
		},
	
		// http://jqueryvalidation.org/filled-selector/
		filled: function( a ) {
			var val = $( a ).val();
			return val !== null && !!$.trim( "" + val );
		},
	
		// http://jqueryvalidation.org/unchecked-selector/
		unchecked: function( a ) {
			return !$( a ).prop( "checked" );
		}
	} );
	
	// Constructor for validator
	$.validator = function( options, form ) {
		this.settings = $.extend( true, {}, $.validator.defaults, options );
		this.currentForm = form;
		this.init();
	};
	
	// http://jqueryvalidation.org/jQuery.validator.format/
	$.validator.format = function( source, params ) {
		if ( arguments.length === 1 ) {
			return function() {
				var args = $.makeArray( arguments );
				args.unshift( source );
				return $.validator.format.apply( this, args );
			};
		}
		if ( params === undefined ) {
			return source;
		}
		if ( arguments.length > 2 && params.constructor !== Array  ) {
			params = $.makeArray( arguments ).slice( 1 );
		}
		if ( params.constructor !== Array ) {
			params = [ params ];
		}
		$.each( params, function( i, n ) {
			source = source.replace( new RegExp( "\\{" + i + "\\}", "g" ), function() {
				return n;
			} );
		} );
		return source;
	};
	
	$.extend( $.validator, {
	
		defaults: {
			messages: {},
			groups: {},
			rules: {},
			errorClass: "error",
			pendingClass: "pending",
			validClass: "valid",
			errorElement: "label",
			focusCleanup: false,
			focusInvalid: true,
			errorContainer: $( [] ),
			errorLabelContainer: $( [] ),
			onsubmit: true,
			ignore: ":hidden",
			ignoreTitle: false,
			onfocusin: function( element ) {
				this.lastActive = element;
	
				// Hide error label and remove error class on focus if enabled
				if ( this.settings.focusCleanup ) {
					if ( this.settings.unhighlight ) {
						this.settings.unhighlight.call( this, element, this.settings.errorClass, this.settings.validClass );
					}
					this.hideThese( this.errorsFor( element ) );
				}
			},
			onfocusout: function( element ) {
				if ( !this.checkable( element ) && ( element.name in this.submitted || !this.optional( element ) ) ) {
					this.element( element );
				}
			},
			onkeyup: function( element, event ) {
	
				// Avoid revalidate the field when pressing one of the following keys
				// Shift       => 16
				// Ctrl        => 17
				// Alt         => 18
				// Caps lock   => 20
				// End         => 35
				// Home        => 36
				// Left arrow  => 37
				// Up arrow    => 38
				// Right arrow => 39
				// Down arrow  => 40
				// Insert      => 45
				// Num lock    => 144
				// AltGr key   => 225
				var excludedKeys = [
					16, 17, 18, 20, 35, 36, 37,
					38, 39, 40, 45, 144, 225
				];
	
				if ( event.which === 9 && this.elementValue( element ) === "" || $.inArray( event.keyCode, excludedKeys ) !== -1 ) {
					return;
				} else if ( element.name in this.submitted || element.name in this.invalid ) {
					this.element( element );
				}
			},
			onclick: function( element ) {
	
				// Click on selects, radiobuttons and checkboxes
				if ( element.name in this.submitted ) {
					this.element( element );
	
				// Or option elements, check parent select in that case
				} else if ( element.parentNode.name in this.submitted ) {
					this.element( element.parentNode );
				}
			},
			highlight: function( element, errorClass, validClass ) {
				if ( element.type === "radio" ) {
					this.findByName( element.name ).addClass( errorClass ).removeClass( validClass );
				} else {
					$( element ).addClass( errorClass ).removeClass( validClass );
				}
			},
			unhighlight: function( element, errorClass, validClass ) {
				if ( element.type === "radio" ) {
					this.findByName( element.name ).removeClass( errorClass ).addClass( validClass );
				} else {
					$( element ).removeClass( errorClass ).addClass( validClass );
				}
			}
		},
	
		// http://jqueryvalidation.org/jQuery.validator.setDefaults/
		setDefaults: function( settings ) {
			$.extend( $.validator.defaults, settings );
		},
	
		messages: {
			required: "This field is required.",
			remote: "Please fix this field.",
			email: "Please enter a valid email address.",
			url: "Please enter a valid URL.",
			date: "Please enter a valid date.",
			dateISO: "Please enter a valid date (ISO).",
			number: "Please enter a valid number.",
			digits: "Please enter only digits.",
			equalTo: "Please enter the same value again.",
			maxlength: $.validator.format( "Please enter no more than {0} characters." ),
			minlength: $.validator.format( "Please enter at least {0} characters." ),
			rangelength: $.validator.format( "Please enter a value between {0} and {1} characters long." ),
			range: $.validator.format( "Please enter a value between {0} and {1}." ),
			max: $.validator.format( "Please enter a value less than or equal to {0}." ),
			min: $.validator.format( "Please enter a value greater than or equal to {0}." ),
			step: $.validator.format( "Please enter a multiple of {0}." )
		},
	
		autoCreateRanges: false,
	
		prototype: {
	
			init: function() {
				this.labelContainer = $( this.settings.errorLabelContainer );
				this.errorContext = this.labelContainer.length && this.labelContainer || $( this.currentForm );
				this.containers = $( this.settings.errorContainer ).add( this.settings.errorLabelContainer );
				this.submitted = {};
				this.valueCache = {};
				this.pendingRequest = 0;
				this.pending = {};
				this.invalid = {};
				this.reset();
	
				var groups = ( this.groups = {} ),
					rules;
				$.each( this.settings.groups, function( key, value ) {
					if ( typeof value === "string" ) {
						value = value.split( /\s/ );
					}
					$.each( value, function( index, name ) {
						groups[ name ] = key;
					} );
				} );
				rules = this.settings.rules;
				$.each( rules, function( key, value ) {
					rules[ key ] = $.validator.normalizeRule( value );
				} );
	
				function delegate( event ) {
	
					// Set form expando on contenteditable
					if ( !this.form && this.hasAttribute( "contenteditable" ) ) {
						this.form = $( this ).closest( "form" )[ 0 ];
					}
	
					var validator = $.data( this.form, "validator" ),
						eventType = "on" + event.type.replace( /^validate/, "" ),
						settings = validator.settings;
					if ( settings[ eventType ] && !$( this ).is( settings.ignore ) ) {
						settings[ eventType ].call( validator, this, event );
					}
				}
	
				$( this.currentForm )
					.on( "focusin.validate focusout.validate keyup.validate",
						":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'], " +
						"[type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], " +
						"[type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], " +
						"[type='radio'], [type='checkbox'], [contenteditable]", delegate )
	
					// Support: Chrome, oldIE
					// "select" is provided as event.target when clicking a option
					.on( "click.validate", "select, option, [type='radio'], [type='checkbox']", delegate );
	
				if ( this.settings.invalidHandler ) {
					$( this.currentForm ).on( "invalid-form.validate", this.settings.invalidHandler );
				}
	
				// Add aria-required to any Static/Data/Class required fields before first validation
				// Screen readers require this attribute to be present before the initial submission http://www.w3.org/TR/WCAG-TECHS/ARIA2.html
				$( this.currentForm ).find( "[required], [data-rule-required], .required" ).attr( "aria-required", "true" );
			},
	
			// http://jqueryvalidation.org/Validator.form/
			form: function() {
				this.checkForm();
				$.extend( this.submitted, this.errorMap );
				this.invalid = $.extend( {}, this.errorMap );
				if ( !this.valid() ) {
					$( this.currentForm ).triggerHandler( "invalid-form", [ this ] );
				}
				this.showErrors();
				return this.valid();
			},
	
			checkForm: function() {
				this.prepareForm();
				for ( var i = 0, elements = ( this.currentElements = this.elements() ); elements[ i ]; i++ ) {
					this.check( elements[ i ] );
				}
				return this.valid();
			},
	
			// http://jqueryvalidation.org/Validator.element/
			element: function( element ) {
				var cleanElement = this.clean( element ),
					checkElement = this.validationTargetFor( cleanElement ),
					v = this,
					result = true,
					rs, group;
	
				if ( checkElement === undefined ) {
					delete this.invalid[ cleanElement.name ];
				} else {
					this.prepareElement( checkElement );
					this.currentElements = $( checkElement );
	
					// If this element is grouped, then validate all group elements already
					// containing a value
					group = this.groups[ checkElement.name ];
					if ( group ) {
						$.each( this.groups, function( name, testgroup ) {
							if ( testgroup === group && name !== checkElement.name ) {
								cleanElement = v.validationTargetFor( v.clean( v.findByName( name ) ) );
								if ( cleanElement && cleanElement.name in v.invalid ) {
									v.currentElements.push( cleanElement );
									result = v.check( cleanElement ) && result;
								}
							}
						} );
					}
	
					rs = this.check( checkElement ) !== false;
					result = result && rs;
					if ( rs ) {
						this.invalid[ checkElement.name ] = false;
					} else {
						this.invalid[ checkElement.name ] = true;
					}
	
					if ( !this.numberOfInvalids() ) {
	
						// Hide error containers on last error
						this.toHide = this.toHide.add( this.containers );
					}
					this.showErrors();
	
					// Add aria-invalid status for screen readers
					$( element ).attr( "aria-invalid", !rs );
				}
	
				return result;
			},
	
			// http://jqueryvalidation.org/Validator.showErrors/
			showErrors: function( errors ) {
				if ( errors ) {
					var validator = this;
	
					// Add items to error list and map
					$.extend( this.errorMap, errors );
					this.errorList = $.map( this.errorMap, function( message, name ) {
						return {
							message: message,
							element: validator.findByName( name )[ 0 ]
						};
					} );
	
					// Remove items from success list
					this.successList = $.grep( this.successList, function( element ) {
						return !( element.name in errors );
					} );
				}
				if ( this.settings.showErrors ) {
					this.settings.showErrors.call( this, this.errorMap, this.errorList );
				} else {
					this.defaultShowErrors();
				}
			},
	
			// http://jqueryvalidation.org/Validator.resetForm/
			resetForm: function() {
				if ( $.fn.resetForm ) {
					$( this.currentForm ).resetForm();
				}
				this.invalid = {};
				this.submitted = {};
				this.prepareForm();
				this.hideErrors();
				var elements = this.elements()
					.removeData( "previousValue" )
					.removeAttr( "aria-invalid" );
	
				this.resetElements( elements );
			},
	
			resetElements: function( elements ) {
				var i;
	
				if ( this.settings.unhighlight ) {
					for ( i = 0; elements[ i ]; i++ ) {
						this.settings.unhighlight.call( this, elements[ i ],
							this.settings.errorClass, "" );
						this.findByName( elements[ i ].name ).removeClass( this.settings.validClass );
					}
				} else {
					elements
						.removeClass( this.settings.errorClass )
						.removeClass( this.settings.validClass );
				}
			},
	
			numberOfInvalids: function() {
				return this.objectLength( this.invalid );
			},
	
			objectLength: function( obj ) {
				/* jshint unused: false */
				var count = 0,
					i;
				for ( i in obj ) {
					if ( obj[ i ] ) {
						count++;
					}
				}
				return count;
			},
	
			hideErrors: function() {
				this.hideThese( this.toHide );
			},
	
			hideThese: function( errors ) {
				errors.not( this.containers ).text( "" );
				this.addWrapper( errors ).hide();
			},
	
			valid: function() {
				return this.size() === 0;
			},
	
			size: function() {
				return this.errorList.length;
			},
	
			focusInvalid: function() {
				if ( this.settings.focusInvalid ) {
					try {
						$( this.findLastActive() || this.errorList.length && this.errorList[ 0 ].element || [] )
						.filter( ":visible" )
						.focus()
	
						// Manually trigger focusin event; without it, focusin handler isn't called, findLastActive won't have anything to find
						.trigger( "focusin" );
					} catch ( e ) {
	
						// Ignore IE throwing errors when focusing hidden elements
					}
				}
			},
	
			findLastActive: function() {
				var lastActive = this.lastActive;
				return lastActive && $.grep( this.errorList, function( n ) {
					return n.element.name === lastActive.name;
				} ).length === 1 && lastActive;
			},
	
			elements: function() {
				var validator = this,
					rulesCache = {};
	
				// Select all valid inputs inside the form (no submit or reset buttons)
				return $( this.currentForm )
				.find( "input, select, textarea, [contenteditable]" )
				.not( ":submit, :reset, :image, :disabled" )
				.not( this.settings.ignore )
				.filter( function() {
					var name = this.name || $( this ).attr( "name" ); // For contenteditable
					if ( !name && validator.settings.debug && window.console ) {
						console.error( "%o has no name assigned", this );
					}
	
					// Set form expando on contenteditable
					if ( this.hasAttribute( "contenteditable" ) ) {
						this.form = $( this ).closest( "form" )[ 0 ];
					}
	
					// Select only the first element for each name, and only those with rules specified
					if ( name in rulesCache || !validator.objectLength( $( this ).rules() ) ) {
						return false;
					}
	
					rulesCache[ name ] = true;
					return true;
				} );
			},
	
			clean: function( selector ) {
				return $( selector )[ 0 ];
			},
	
			errors: function() {
				var errorClass = this.settings.errorClass.split( " " ).join( "." );
				return $( this.settings.errorElement + "." + errorClass, this.errorContext );
			},
	
			resetInternals: function() {
				this.successList = [];
				this.errorList = [];
				this.errorMap = {};
				this.toShow = $( [] );
				this.toHide = $( [] );
			},
	
			reset: function() {
				this.resetInternals();
				this.currentElements = $( [] );
			},
	
			prepareForm: function() {
				this.reset();
				this.toHide = this.errors().add( this.containers );
			},
	
			prepareElement: function( element ) {
				this.reset();
				this.toHide = this.errorsFor( element );
			},
	
			elementValue: function( element ) {
				var $element = $( element ),
					type = element.type,
					val, idx;
	
				if ( type === "radio" || type === "checkbox" ) {
					return this.findByName( element.name ).filter( ":checked" ).val();
				} else if ( type === "number" && typeof element.validity !== "undefined" ) {
					return element.validity.badInput ? "NaN" : $element.val();
				}
	
				if ( element.hasAttribute( "contenteditable" ) ) {
					val = $element.text();
				} else {
					val = $element.val();
				}
	
				if ( type === "file" ) {
	
					// Modern browser (chrome & safari)
					if ( val.substr( 0, 12 ) === "C:\\fakepath\\" ) {
						return val.substr( 12 );
					}
	
					// Legacy browsers
					// Unix-based path
					idx = val.lastIndexOf( "/" );
					if ( idx >= 0 ) {
						return val.substr( idx + 1 );
					}
	
					// Windows-based path
					idx = val.lastIndexOf( "\\" );
					if ( idx >= 0 ) {
						return val.substr( idx + 1 );
					}
	
					// Just the file name
					return val;
				}
	
				if ( typeof val === "string" ) {
					return val.replace( /\r/g, "" );
				}
				return val;
			},
	
			check: function( element ) {
				element = this.validationTargetFor( this.clean( element ) );
	
				var rules = $( element ).rules(),
					rulesCount = $.map( rules, function( n, i ) {
						return i;
					} ).length,
					dependencyMismatch = false,
					val = this.elementValue( element ),
					result, method, rule;
	
				// If a normalizer is defined for this element, then
				// call it to retreive the changed value instead
				// of using the real one.
				// Note that `this` in the normalizer is `element`.
				if ( typeof rules.normalizer === "function" ) {
					val = rules.normalizer.call( element, val );
	
					if ( typeof val !== "string" ) {
						throw new TypeError( "The normalizer should return a string value." );
					}
	
					// Delete the normalizer from rules to avoid treating
					// it as a pre-defined method.
					delete rules.normalizer;
				}
	
				for ( method in rules ) {
					rule = { method: method, parameters: rules[ method ] };
					try {
						result = $.validator.methods[ method ].call( this, val, element, rule.parameters );
	
						// If a method indicates that the field is optional and therefore valid,
						// don't mark it as valid when there are no other rules
						if ( result === "dependency-mismatch" && rulesCount === 1 ) {
							dependencyMismatch = true;
							continue;
						}
						dependencyMismatch = false;
	
						if ( result === "pending" ) {
							this.toHide = this.toHide.not( this.errorsFor( element ) );
							return;
						}
	
						if ( !result ) {
							this.formatAndAdd( element, rule );
							return false;
						}
					} catch ( e ) {
						if ( this.settings.debug && window.console ) {
							console.log( "Exception occurred when checking element " + element.id + ", check the '" + rule.method + "' method.", e );
						}
						if ( e instanceof TypeError ) {
							e.message += ".  Exception occurred when checking element " + element.id + ", check the '" + rule.method + "' method.";
						}
	
						throw e;
					}
				}
				if ( dependencyMismatch ) {
					return;
				}
				if ( this.objectLength( rules ) ) {
					this.successList.push( element );
				}
				return true;
			},
	
			// Return the custom message for the given element and validation method
			// specified in the element's HTML5 data attribute
			// return the generic message if present and no method specific message is present
			customDataMessage: function( element, method ) {
				return $( element ).data( "msg" + method.charAt( 0 ).toUpperCase() +
					method.substring( 1 ).toLowerCase() ) || $( element ).data( "msg" );
			},
	
			// Return the custom message for the given element name and validation method
			customMessage: function( name, method ) {
				var m = this.settings.messages[ name ];
				return m && ( m.constructor === String ? m : m[ method ] );
			},
	
			// Return the first defined argument, allowing empty strings
			findDefined: function() {
				for ( var i = 0; i < arguments.length; i++ ) {
					if ( arguments[ i ] !== undefined ) {
						return arguments[ i ];
					}
				}
				return undefined;
			},
	
			// The second parameter 'rule' used to be a string, and extended to an object literal
			// of the following form:
			// rule = {
			//     method: "method name",
			//     parameters: "the given method parameters"
			// }
			//
			// The old behavior still supported, kept to maintain backward compatibility with
			// old code, and will be removed in the next major release.
			defaultMessage: function( element, rule ) {
				if ( typeof rule === "string" ) {
					rule = { method: rule };
				}
	
				var message = this.findDefined(
						this.customMessage( element.name, rule.method ),
						this.customDataMessage( element, rule.method ),
	
						// 'title' is never undefined, so handle empty string as undefined
						!this.settings.ignoreTitle && element.title || undefined,
						$.validator.messages[ rule.method ],
						"<strong>Warning: No message defined for " + element.name + "</strong>"
					),
					theregex = /\$?\{(\d+)\}/g;
				if ( typeof message === "function" ) {
					message = message.call( this, rule.parameters, element );
				} else if ( theregex.test( message ) ) {
					message = $.validator.format( message.replace( theregex, "{$1}" ), rule.parameters );
				}
	
				return message;
			},
	
			formatAndAdd: function( element, rule ) {
				var message = this.defaultMessage( element, rule );
	
				this.errorList.push( {
					message: message,
					element: element,
					method: rule.method
				} );
	
				this.errorMap[ element.name ] = message;
				this.submitted[ element.name ] = message;
			},
	
			addWrapper: function( toToggle ) {
				if ( this.settings.wrapper ) {
					toToggle = toToggle.add( toToggle.parent( this.settings.wrapper ) );
				}
				return toToggle;
			},
	
			defaultShowErrors: function() {
				var i, elements, error;
				for ( i = 0; this.errorList[ i ]; i++ ) {
					error = this.errorList[ i ];
					if ( this.settings.highlight ) {
						this.settings.highlight.call( this, error.element, this.settings.errorClass, this.settings.validClass );
					}
					this.showLabel( error.element, error.message );
				}
				if ( this.errorList.length ) {
					this.toShow = this.toShow.add( this.containers );
				}
				if ( this.settings.success ) {
					for ( i = 0; this.successList[ i ]; i++ ) {
						this.showLabel( this.successList[ i ] );
					}
				}
				if ( this.settings.unhighlight ) {
					for ( i = 0, elements = this.validElements(); elements[ i ]; i++ ) {
						this.settings.unhighlight.call( this, elements[ i ], this.settings.errorClass, this.settings.validClass );
					}
				}
				this.toHide = this.toHide.not( this.toShow );
				this.hideErrors();
				this.addWrapper( this.toShow ).show();
			},
	
			validElements: function() {
				return this.currentElements.not( this.invalidElements() );
			},
	
			invalidElements: function() {
				return $( this.errorList ).map( function() {
					return this.element;
				} );
			},
	
			showLabel: function( element, message ) {
				var place, group, errorID, v,
					error = this.errorsFor( element ),
					elementID = this.idOrName( element ),
					describedBy = $( element ).attr( "aria-describedby" );
	
				if ( error.length ) {
	
					// Refresh error/success class
					error.removeClass( this.settings.validClass ).addClass( this.settings.errorClass );
	
					// Replace message on existing label
					error.html( message );
				} else {
	
					// Create error element
					error = $( "<" + this.settings.errorElement + ">" )
						.attr( "id", elementID + "-error" )
						.addClass( this.settings.errorClass )
						.html( message || "" );
	
					// Maintain reference to the element to be placed into the DOM
					place = error;
					if ( this.settings.wrapper ) {
	
						// Make sure the element is visible, even in IE
						// actually showing the wrapped element is handled elsewhere
						place = error.hide().show().wrap( "<" + this.settings.wrapper + "/>" ).parent();
					}
					if ( this.labelContainer.length ) {
						this.labelContainer.append( place );
					} else if ( this.settings.errorPlacement ) {
						this.settings.errorPlacement.call( this, place, $( element ) );
					} else {
						place.insertAfter( element );
					}
	
					// Link error back to the element
					if ( error.is( "label" ) ) {
	
						// If the error is a label, then associate using 'for'
						error.attr( "for", elementID );
	
						// If the element is not a child of an associated label, then it's necessary
						// to explicitly apply aria-describedby
					} else if ( error.parents( "label[for='" + this.escapeCssMeta( elementID ) + "']" ).length === 0 ) {
						errorID = error.attr( "id" );
	
						// Respect existing non-error aria-describedby
						if ( !describedBy ) {
							describedBy = errorID;
						} else if ( !describedBy.match( new RegExp( "\\b" + this.escapeCssMeta( errorID ) + "\\b" ) ) ) {
	
							// Add to end of list if not already present
							describedBy += " " + errorID;
						}
						$( element ).attr( "aria-describedby", describedBy );
	
						// If this element is grouped, then assign to all elements in the same group
						group = this.groups[ element.name ];
						if ( group ) {
							v = this;
							$.each( v.groups, function( name, testgroup ) {
								if ( testgroup === group ) {
									$( "[name='" + v.escapeCssMeta( name ) + "']", v.currentForm )
										.attr( "aria-describedby", error.attr( "id" ) );
								}
							} );
						}
					}
				}
				if ( !message && this.settings.success ) {
					error.text( "" );
					if ( typeof this.settings.success === "string" ) {
						error.addClass( this.settings.success );
					} else {
						this.settings.success( error, element );
					}
				}
				this.toShow = this.toShow.add( error );
			},
	
			errorsFor: function( element ) {
				var name = this.escapeCssMeta( this.idOrName( element ) ),
					describer = $( element ).attr( "aria-describedby" ),
					selector = "label[for='" + name + "'], label[for='" + name + "'] *";
	
				// 'aria-describedby' should directly reference the error element
				if ( describer ) {
					selector = selector + ", #" + this.escapeCssMeta( describer )
						.replace( /\s+/g, ", #" );
				}
	
				return this
					.errors()
					.filter( selector );
			},
	
			// See https://api.jquery.com/category/selectors/, for CSS
			// meta-characters that should be escaped in order to be used with JQuery
			// as a literal part of a name/id or any selector.
			escapeCssMeta: function( string ) {
				return string.replace( /([\\!"#$%&'()*+,./:;<=>?@\[\]^`{|}~])/g, "\\$1" );
			},
	
			idOrName: function( element ) {
				return this.groups[ element.name ] || ( this.checkable( element ) ? element.name : element.id || element.name );
			},
	
			validationTargetFor: function( element ) {
	
				// If radio/checkbox, validate first element in group instead
				if ( this.checkable( element ) ) {
					element = this.findByName( element.name );
				}
	
				// Always apply ignore filter
				return $( element ).not( this.settings.ignore )[ 0 ];
			},
	
			checkable: function( element ) {
				return ( /radio|checkbox/i ).test( element.type );
			},
	
			findByName: function( name ) {
				return $( this.currentForm ).find( "[name='" + this.escapeCssMeta( name ) + "']" );
			},
	
			getLength: function( value, element ) {
				switch ( element.nodeName.toLowerCase() ) {
				case "select":
					return $( "option:selected", element ).length;
				case "input":
					if ( this.checkable( element ) ) {
						return this.findByName( element.name ).filter( ":checked" ).length;
					}
				}
				return value.length;
			},
	
			depend: function( param, element ) {
				return this.dependTypes[ typeof param ] ? this.dependTypes[ typeof param ]( param, element ) : true;
			},
	
			dependTypes: {
				"boolean": function( param ) {
					return param;
				},
				"string": function( param, element ) {
					return !!$( param, element.form ).length;
				},
				"function": function( param, element ) {
					return param( element );
				}
			},
	
			optional: function( element ) {
				var val = this.elementValue( element );
				return !$.validator.methods.required.call( this, val, element ) && "dependency-mismatch";
			},
	
			startRequest: function( element ) {
				if ( !this.pending[ element.name ] ) {
					this.pendingRequest++;
					$( element ).addClass( this.settings.pendingClass );
					this.pending[ element.name ] = true;
				}
			},
	
			stopRequest: function( element, valid ) {
				this.pendingRequest--;
	
				// Sometimes synchronization fails, make sure pendingRequest is never < 0
				if ( this.pendingRequest < 0 ) {
					this.pendingRequest = 0;
				}
				delete this.pending[ element.name ];
				$( element ).removeClass( this.settings.pendingClass );
				if ( valid && this.pendingRequest === 0 && this.formSubmitted && this.form() ) {
					$( this.currentForm ).submit();
					this.formSubmitted = false;
				} else if ( !valid && this.pendingRequest === 0 && this.formSubmitted ) {
					$( this.currentForm ).triggerHandler( "invalid-form", [ this ] );
					this.formSubmitted = false;
				}
			},
	
			previousValue: function( element, method ) {
				method = typeof method === "string" && method || "remote";
	
				return $.data( element, "previousValue" ) || $.data( element, "previousValue", {
					old: null,
					valid: true,
					message: this.defaultMessage( element, { method: method } )
				} );
			},
	
			// Cleans up all forms and elements, removes validator-specific events
			destroy: function() {
				this.resetForm();
	
				$( this.currentForm )
					.off( ".validate" )
					.removeData( "validator" )
					.find( ".validate-equalTo-blur" )
						.off( ".validate-equalTo" )
						.removeClass( "validate-equalTo-blur" );
			}
	
		},
	
		classRuleSettings: {
			required: { required: true },
			email: { email: true },
			url: { url: true },
			date: { date: true },
			dateISO: { dateISO: true },
			number: { number: true },
			digits: { digits: true },
			creditcard: { creditcard: true }
		},
	
		addClassRules: function( className, rules ) {
			if ( className.constructor === String ) {
				this.classRuleSettings[ className ] = rules;
			} else {
				$.extend( this.classRuleSettings, className );
			}
		},
	
		classRules: function( element ) {
			var rules = {},
				classes = $( element ).attr( "class" );
	
			if ( classes ) {
				$.each( classes.split( " " ), function() {
					if ( this in $.validator.classRuleSettings ) {
						$.extend( rules, $.validator.classRuleSettings[ this ] );
					}
				} );
			}
			return rules;
		},
	
		normalizeAttributeRule: function( rules, type, method, value ) {
	
			// Convert the value to a number for number inputs, and for text for backwards compability
			// allows type="date" and others to be compared as strings
			if ( /min|max|step/.test( method ) && ( type === null || /number|range|text/.test( type ) ) ) {
				value = Number( value );
	
				// Support Opera Mini, which returns NaN for undefined minlength
				if ( isNaN( value ) ) {
					value = undefined;
				}
			}
	
			if ( value || value === 0 ) {
				rules[ method ] = value;
			} else if ( type === method && type !== "range" ) {
	
				// Exception: the jquery validate 'range' method
				// does not test for the html5 'range' type
				rules[ method ] = true;
			}
		},
	
		attributeRules: function( element ) {
			var rules = {},
				$element = $( element ),
				type = element.getAttribute( "type" ),
				method, value;
	
			for ( method in $.validator.methods ) {
	
				// Support for <input required> in both html5 and older browsers
				if ( method === "required" ) {
					value = element.getAttribute( method );
	
					// Some browsers return an empty string for the required attribute
					// and non-HTML5 browsers might have required="" markup
					if ( value === "" ) {
						value = true;
					}
	
					// Force non-HTML5 browsers to return bool
					value = !!value;
				} else {
					value = $element.attr( method );
				}
	
				this.normalizeAttributeRule( rules, type, method, value );
			}
	
			// 'maxlength' may be returned as -1, 2147483647 ( IE ) and 524288 ( safari ) for text inputs
			if ( rules.maxlength && /-1|2147483647|524288/.test( rules.maxlength ) ) {
				delete rules.maxlength;
			}
	
			return rules;
		},
	
		dataRules: function( element ) {
			var rules = {},
				$element = $( element ),
				type = element.getAttribute( "type" ),
				method, value;
	
			for ( method in $.validator.methods ) {
				value = $element.data( "rule" + method.charAt( 0 ).toUpperCase() + method.substring( 1 ).toLowerCase() );
				this.normalizeAttributeRule( rules, type, method, value );
			}
			return rules;
		},
	
		staticRules: function( element ) {
			var rules = {},
				validator = $.data( element.form, "validator" );
	
			if ( validator.settings.rules ) {
				rules = $.validator.normalizeRule( validator.settings.rules[ element.name ] ) || {};
			}
			return rules;
		},
	
		normalizeRules: function( rules, element ) {
	
			// Handle dependency check
			$.each( rules, function( prop, val ) {
	
				// Ignore rule when param is explicitly false, eg. required:false
				if ( val === false ) {
					delete rules[ prop ];
					return;
				}
				if ( val.param || val.depends ) {
					var keepRule = true;
					switch ( typeof val.depends ) {
					case "string":
						keepRule = !!$( val.depends, element.form ).length;
						break;
					case "function":
						keepRule = val.depends.call( element, element );
						break;
					}
					if ( keepRule ) {
						rules[ prop ] = val.param !== undefined ? val.param : true;
					} else {
						$.data( element.form, "validator" ).resetElements( $( element ) );
						delete rules[ prop ];
					}
				}
			} );
	
			// Evaluate parameters
			$.each( rules, function( rule, parameter ) {
				rules[ rule ] = $.isFunction( parameter ) && rule !== "normalizer" ? parameter( element ) : parameter;
			} );
	
			// Clean number parameters
			$.each( [ "minlength", "maxlength" ], function() {
				if ( rules[ this ] ) {
					rules[ this ] = Number( rules[ this ] );
				}
			} );
			$.each( [ "rangelength", "range" ], function() {
				var parts;
				if ( rules[ this ] ) {
					if ( $.isArray( rules[ this ] ) ) {
						rules[ this ] = [ Number( rules[ this ][ 0 ] ), Number( rules[ this ][ 1 ] ) ];
					} else if ( typeof rules[ this ] === "string" ) {
						parts = rules[ this ].replace( /[\[\]]/g, "" ).split( /[\s,]+/ );
						rules[ this ] = [ Number( parts[ 0 ] ), Number( parts[ 1 ] ) ];
					}
				}
			} );
	
			if ( $.validator.autoCreateRanges ) {
	
				// Auto-create ranges
				if ( rules.min != null && rules.max != null ) {
					rules.range = [ rules.min, rules.max ];
					delete rules.min;
					delete rules.max;
				}
				if ( rules.minlength != null && rules.maxlength != null ) {
					rules.rangelength = [ rules.minlength, rules.maxlength ];
					delete rules.minlength;
					delete rules.maxlength;
				}
			}
	
			return rules;
		},
	
		// Converts a simple string to a {string: true} rule, e.g., "required" to {required:true}
		normalizeRule: function( data ) {
			if ( typeof data === "string" ) {
				var transformed = {};
				$.each( data.split( /\s/ ), function() {
					transformed[ this ] = true;
				} );
				data = transformed;
			}
			return data;
		},
	
		// http://jqueryvalidation.org/jQuery.validator.addMethod/
		addMethod: function( name, method, message ) {
			$.validator.methods[ name ] = method;
			$.validator.messages[ name ] = message !== undefined ? message : $.validator.messages[ name ];
			if ( method.length < 3 ) {
				$.validator.addClassRules( name, $.validator.normalizeRule( name ) );
			}
		},
	
		// http://jqueryvalidation.org/jQuery.validator.methods/
		methods: {
	
			// http://jqueryvalidation.org/required-method/
			required: function( value, element, param ) {
	
				// Check if dependency is met
				if ( !this.depend( param, element ) ) {
					return "dependency-mismatch";
				}
				if ( element.nodeName.toLowerCase() === "select" ) {
	
					// Could be an array for select-multiple or a string, both are fine this way
					var val = $( element ).val();
					return val && val.length > 0;
				}
				if ( this.checkable( element ) ) {
					return this.getLength( value, element ) > 0;
				}
				return value.length > 0;
			},
	
			// http://jqueryvalidation.org/email-method/
			email: function( value, element ) {
	
				// From https://html.spec.whatwg.org/multipage/forms.html#valid-e-mail-address
				// Retrieved 2014-01-14
				// If you have a problem with this implementation, report a bug against the above spec
				// Or use custom methods to implement your own email validation
				return this.optional( element ) || /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test( value );
			},
	
			// http://jqueryvalidation.org/url-method/
			url: function( value, element ) {
	
				// Copyright (c) 2010-2013 Diego Perini, MIT licensed
				// https://gist.github.com/dperini/729294
				// see also https://mathiasbynens.be/demo/url-regex
				// modified to allow protocol-relative URLs
				return this.optional( element ) || /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i.test( value );
			},
	
			// http://jqueryvalidation.org/date-method/
			date: function( value, element ) {
				return this.optional( element ) || !/Invalid|NaN/.test( new Date( value ).toString() );
			},
	
			// http://jqueryvalidation.org/dateISO-method/
			dateISO: function( value, element ) {
				return this.optional( element ) || /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test( value );
			},
	
			// http://jqueryvalidation.org/number-method/
			number: function( value, element ) {
				return this.optional( element ) || /^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test( value );
			},
	
			// http://jqueryvalidation.org/digits-method/
			digits: function( value, element ) {
				return this.optional( element ) || /^\d+$/.test( value );
			},
	
			// http://jqueryvalidation.org/minlength-method/
			minlength: function( value, element, param ) {
				var length = $.isArray( value ) ? value.length : this.getLength( value, element );
				return this.optional( element ) || length >= param;
			},
	
			// http://jqueryvalidation.org/maxlength-method/
			maxlength: function( value, element, param ) {
				var length = $.isArray( value ) ? value.length : this.getLength( value, element );
				return this.optional( element ) || length <= param;
			},
	
			// http://jqueryvalidation.org/rangelength-method/
			rangelength: function( value, element, param ) {
				var length = $.isArray( value ) ? value.length : this.getLength( value, element );
				return this.optional( element ) || ( length >= param[ 0 ] && length <= param[ 1 ] );
			},
	
			// http://jqueryvalidation.org/min-method/
			min: function( value, element, param ) {
				return this.optional( element ) || value >= param;
			},
	
			// http://jqueryvalidation.org/max-method/
			max: function( value, element, param ) {
				return this.optional( element ) || value <= param;
			},
	
			// http://jqueryvalidation.org/range-method/
			range: function( value, element, param ) {
				return this.optional( element ) || ( value >= param[ 0 ] && value <= param[ 1 ] );
			},
	
			// http://jqueryvalidation.org/step-method/
			step: function( value, element, param ) {
				var type = $( element ).attr( "type" ),
					errorMessage = "Step attribute on input type " + type + " is not supported.",
					supportedTypes = [ "text", "number", "range" ],
					re = new RegExp( "\\b" + type + "\\b" ),
					notSupported = type && !re.test( supportedTypes.join() ),
					decimalPlaces = function( num ) {
						var match = ( "" + num ).match( /(?:\.(\d+))?$/ );
						if ( !match ) {
							return 0;
						}
	
						// Number of digits right of decimal point.
						return match[ 1 ] ? match[ 1 ].length : 0;
					},
					toInt = function( num ) {
						return Math.round( num * Math.pow( 10, decimals ) );
					},
					valid = true,
					decimals;
	
				// Works only for text, number and range input types
				// TODO find a way to support input types date, datetime, datetime-local, month, time and week
				if ( notSupported ) {
					throw new Error( errorMessage );
				}
	
				decimals = decimalPlaces( param );
	
				// Value can't have too many decimals
				if ( decimalPlaces( value ) > decimals || toInt( value ) % toInt( param ) !== 0 ) {
					valid = false;
				}
	
				return this.optional( element ) || valid;
			},
	
			// http://jqueryvalidation.org/equalTo-method/
			equalTo: function( value, element, param ) {
	
				// Bind to the blur event of the target in order to revalidate whenever the target field is updated
				var target = $( param );
				if ( this.settings.onfocusout && target.not( ".validate-equalTo-blur" ).length ) {
					target.addClass( "validate-equalTo-blur" ).on( "blur.validate-equalTo", function() {
						$( element ).valid();
					} );
				}
				return value === target.val();
			},
	
			// http://jqueryvalidation.org/remote-method/
			remote: function( value, element, param, method ) {
				if ( this.optional( element ) ) {
					return "dependency-mismatch";
				}
	
				method = typeof method === "string" && method || "remote";
	
				var previous = this.previousValue( element, method ),
					validator, data, optionDataString;
	
				if ( !this.settings.messages[ element.name ] ) {
					this.settings.messages[ element.name ] = {};
				}
				previous.originalMessage = previous.originalMessage || this.settings.messages[ element.name ][ method ];
				this.settings.messages[ element.name ][ method ] = previous.message;
	
				param = typeof param === "string" && { url: param } || param;
				optionDataString = $.param( $.extend( { data: value }, param.data ) );
				if ( previous.old === optionDataString ) {
					return previous.valid;
				}
	
				previous.old = optionDataString;
				validator = this;
				this.startRequest( element );
				data = {};
				data[ element.name ] = value;
				$.ajax( $.extend( true, {
					mode: "abort",
					port: "validate" + element.name,
					dataType: "json",
					data: data,
					context: validator.currentForm,
					success: function( response ) {
						var valid = response === true || response === "true",
							errors, message, submitted;
	
						validator.settings.messages[ element.name ][ method ] = previous.originalMessage;
						if ( valid ) {
							submitted = validator.formSubmitted;
							validator.resetInternals();
							validator.toHide = validator.errorsFor( element );
							validator.formSubmitted = submitted;
							validator.successList.push( element );
							validator.invalid[ element.name ] = false;
							validator.showErrors();
						} else {
							errors = {};
							message = response || validator.defaultMessage( element, { method: method, parameters: value } );
							errors[ element.name ] = previous.message = message;
							validator.invalid[ element.name ] = true;
							validator.showErrors( errors );
						}
						previous.valid = valid;
						validator.stopRequest( element, valid );
					}
				}, param ) );
				return "pending";
			}
		}
	
	} );
	
	// Ajax mode: abort
	// usage: $.ajax({ mode: "abort"[, port: "uniqueport"]});
	// if mode:"abort" is used, the previous request on that port (port can be undefined) is aborted via XMLHttpRequest.abort()
	
	var pendingRequests = {},
		ajax;
	
	// Use a prefilter if available (1.5+)
	if ( $.ajaxPrefilter ) {
		$.ajaxPrefilter( function( settings, _, xhr ) {
			var port = settings.port;
			if ( settings.mode === "abort" ) {
				if ( pendingRequests[ port ] ) {
					pendingRequests[ port ].abort();
				}
				pendingRequests[ port ] = xhr;
			}
		} );
	} else {
	
		// Proxy ajax
		ajax = $.ajax;
		$.ajax = function( settings ) {
			var mode = ( "mode" in settings ? settings : $.ajaxSettings ).mode,
				port = ( "port" in settings ? settings : $.ajaxSettings ).port;
			if ( mode === "abort" ) {
				if ( pendingRequests[ port ] ) {
					pendingRequests[ port ].abort();
				}
				pendingRequests[ port ] = ajax.apply( this, arguments );
				return pendingRequests[ port ];
			}
			return ajax.apply( this, arguments );
		};
	}
	
	}));
	}.call(window));

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__("09902a336c15906c385b");


/***/ }),

/***/ 1:
/***/ (function(module, exports) {

	module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=jquery-validation.js.map