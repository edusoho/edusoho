webpackJsonp(["app/js/settings/profile/index"],{

/***/ "da1016b113836a3c7b68":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var InputEdit = function () {
	  function InputEdit(props) {
	    _classCallCheck(this, InputEdit);
	
	    this.el = props.el;
	    this.parent = props.parent || document;
	
	    this.$el = $(this.el);
	
	    this.editBtn = props.editBtn || '.input-edit__edit-btn';
	    this.saveBtn = props.saveBtn || '.input-edit__save-btn';
	    this.cancelBtn = props.cancelBtn || '.input-edit__cancel-btn';
	
	    this.success = props.success || this.success;
	    this.fail = props.fail || this.fail;
	
	    this.init();
	  }
	
	  _createClass(InputEdit, [{
	    key: 'init',
	    value: function init() {
	      this.event();
	    }
	  }, {
	    key: 'event',
	    value: function event() {
	      var _this = this;
	
	      var $parent = $(this.parent);
	
	      $parent.on('click', this.editBtn, function (event) {
	        return _this.edit(event);
	      });
	
	      $parent.on('click', this.saveBtn, function (event) {
	        return _this.save(event);
	      });
	
	      $parent.on('click', this.cancelBtn, function (event) {
	        return _this.cancel(event);
	      });
	    }
	  }, {
	    key: 'edit',
	    value: function edit(event) {
	      var $this = $(event.currentTarget);
	
	      $this.parent().hide();
	
	      this.$el.find('.input-edit__edit-dom').show().find('.input-edit__input').focus().select();
	    }
	  }, {
	    key: 'cancel',
	    value: function cancel(event) {
	      var $this = $(event.currentTarget);
	
	      this.$el.find('.input-edit__edit-dom').hide();
	
	      var text = this.$el.find('.input-edit__static-text').text();
	      this.$el.find('.input-edit__input').val(text);
	
	      this.$el.find('.input-edit__static-dom').show();
	    }
	  }, {
	    key: 'save',
	    value: function save(event) {
	      var _this2 = this;
	
	      var $this = $(event.currentTarget);
	      var url = $this.data('url');
	      var inputName = $this.data('input-name');
	
	      var data = {};
	      data[inputName] = $('input[name=' + inputName + ']').val();
	
	      $this.button('loading');
	
	      $.post(url, data).always(function () {
	        $this.button('reset');
	      }).done(function (data) {
	        var $input = _this2.$el.find('.input-edit__input');
	
	        _this2.$el.find('.input-edit__static-text').text($input.val());
	
	        _this2.$el.find('.input-edit__edit-dom').hide();
	
	        _this2.$el.find('.input-edit__static-dom').show();
	
	        _this2.success(data);
	      }).fail(function (data) {
	        _this2.fail(data);
	      });
	    }
	  }, {
	    key: 'success',
	    value: function success(data) {
	      console.log('success');
	    }
	  }, {
	    key: 'fail',
	    value: function fail(data) {
	      console.log('fail');
	    }
	  }]);
	
	  return InputEdit;
	}();
	
	exports["default"] = InputEdit;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _nickname, _rules;
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _inputEdit = __webpack_require__("da1016b113836a3c7b68");
	
	var _inputEdit2 = _interopRequireDefault(_inputEdit);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	var editor = CKEDITOR.replace('profile_about', {
	  toolbar: 'Simple',
	  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
	});
	
	$(".js-date").datetimepicker({
	  autoclose: true,
	  format: 'yyyy-mm-dd',
	  minView: 'month',
	  language: document.documentElement.lang
	});
	
	$("#user-profile-form").validate({
	  rules: (_rules = {
	    'nickname': (_nickname = {
	      required: true,
	      chinese_alphanumeric: true,
	      byte_minlength: 4,
	      byte_maxlength: 18,
	      nickname: true
	    }, _defineProperty(_nickname, 'chinese_alphanumeric', true), _defineProperty(_nickname, 'es_remote', {
	      type: 'get'
	    }), _nickname),
	    'profile[truename]': {
	      minlength: 2,
	      maxlength: 18
	    },
	    'profile[title]': {
	      maxlength: 24
	    },
	    'profile[qq]': 'qq',
	    'profile[weibo]': 'url',
	    'profile[blog]': 'url',
	    'profile[site]': 'url',
	    'profile[mobile]': 'mobile',
	    'profile[idcard]': 'idcardNumber',
	    'profile[intField1]': { digits: true, maxlength: 9 },
	    'profile[intField2]': { digits: true, maxlength: 9 },
	    'profile[intField3]': { digits: true, maxlength: 9 },
	    'profile[intField4]': { digits: true, maxlength: 9 },
	    'profile[intField5]': { digits: true, maxlength: 9 },
	    'profile[floatField1]': 'float',
	    'profile[floatField2]': 'float',
	    'profile[floatField3]': 'float',
	    'profile[floatField4]': 'float',
	    'profile[floatField5]': 'float',
	    'profile[dateField5]': 'date'
	  }, _defineProperty(_rules, 'profile[dateField5]', 'date'), _defineProperty(_rules, 'profile[dateField5]', 'date'), _defineProperty(_rules, 'profile[dateField5]', 'date'), _defineProperty(_rules, 'profile[dateField5]', 'date'), _rules)
	});
	
	new _inputEdit2["default"]({
	  el: '#nickname-form-group',
	  success: function success(data) {
	    (0, _notify2["default"])('success', Translator.trans(data.message));
	  },
	  fail: function fail(data) {
	    if (data.responseJSON.message) {
	      (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	    } else {
	      (0, _notify2["default"])('danger', Translator.trans('user.settings.basic_info.nickname_change_fail'));
	    }
	  }
	});

/***/ })

});
//# sourceMappingURL=index.js.map