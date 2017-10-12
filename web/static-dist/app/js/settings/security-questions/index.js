webpackJsonp(["app/js/settings/security-questions/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _securityQuestions = __webpack_require__("f9ff574815af2ac7731d");
	
	var _securityQuestions2 = _interopRequireDefault(_securityQuestions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _securityQuestions2["default"]({
	  element: '#settings-security-questions-form',
	  saveBtn: '#password-save-btn'
	});

/***/ }),

/***/ "f9ff574815af2ac7731d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var SecurityQuestion = function () {
	  function SecurityQuestion(props) {
	    _classCallCheck(this, SecurityQuestion);
	
	    this.element = props.element;
	    this.saveBtn = props.saveBtn;
	    this.$q1 = $('[name=question-1]');
	    this.$q2 = $('[name=question-2]');
	    this.$q3 = $('[name=question-3]');
	    this.init();
	  }
	
	  _createClass(SecurityQuestion, [{
	    key: 'init',
	    value: function init() {
	      this.validator();
	      this.initEvent();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var $node = $(this.element);
	      var _this = this;
	
	      $('option[value=parents]').css('display', 'none');
	      $('option[value=teacher]').css('display', 'none');
	      $('option[value=lover]').css('display', 'none');
	
	      this.$q1.on('change', function (event) {
	        var $this = $(this);
	        _this.reflesh_option_display($this);
	      });
	
	      this.$q2.on('change', function (event) {
	        var $this = $(this);
	        _this.reflesh_option_display($this);
	      });
	
	      this.$q3.on('change', function (event) {
	        var $this = $(this);
	        _this.reflesh_option_display($this);
	      });
	    }
	  }, {
	    key: 'validator',
	    value: function validator() {
	      var btn = this.saveBtn;
	      $(this.element).validate({
	        currentDom: btn,
	        ajax: true,
	        rules: {
	          'answer-1': {
	            required: true,
	            maxlength: 20
	          },
	          'answer-2': {
	            required: true,
	            maxlength: 20
	          },
	          'answer-3': {
	            required: true,
	            maxlength: 20
	          },
	          'userLoginPassword': 'required'
	        },
	        submitSuccess: function submitSuccess(data) {
	          (0, _notify2["default"])('success', Translator.trans(data.message));
	
	          $('.modal').modal('hide');
	          window.location.reload();
	        },
	        submitError: function submitError(data) {
	          (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.message));
	        }
	      });
	    }
	  }, {
	    key: 'reflesh_option_display',
	    value: function reflesh_option_display($node) {
	
	      if (this.$q1.val() === this.$q2.val() || this.$q3.val() === this.$q2.val() || this.$q1.val() === this.$q3.val()) {
	        (0, _notify2["default"])('danger', Translator.trans('user.settings.security.security_questions.type_duplicate_hint'));
	        this.$q1.val('parents');
	        this.$q2.val('teacher');
	        this.$q3.val('lover');
	      } else {
	        $('option[value=' + $node.val() + ']').css('display', 'none');
	      }
	
	      var questions = ['parents', 'teacher', 'lover', 'schoolName', 'firstTeacher', 'hobby', 'notSelected'];
	
	      for (var questionId in questions) {
	        if (questions[questionId] !== this.$q1.val() && questions[questionId] !== this.$q2.val() && questions[questionId] !== this.$q3.val()) {
	          $('option[value=' + questions[questionId] + ']').css('display', 'block');
	        }
	      }
	    }
	  }]);
	
	  return SecurityQuestion;
	}();
	
	exports["default"] = SecurityQuestion;

/***/ })

});
//# sourceMappingURL=index.js.map