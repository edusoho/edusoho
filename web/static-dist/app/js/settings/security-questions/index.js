webpackJsonp(["app/js/settings/security-questions/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import notify from 'common/notify';
	
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
	      var validator = this.validator();
	      this.initEvent(validator);
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent(validator) {
	      var _this2 = this;
	
	      var $node = $(this.element);
	      var _this = this;
	
	      $(this.saveBtn).on('click', function (event) {
	        var $this = $(event.currentTarget);
	
	        if (validator.form()) {
	          $this.button('loading');
	
	          $(_this2.element).submit();
	        }
	      });
	
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
	      var validator = $(this.element).validate({
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
	        }
	      });
	
	      return validator;
	    }
	  }, {
	    key: 'reflesh_option_display',
	    value: function reflesh_option_display($node) {
	
	      if (this.$q1.val() === this.$q2.val() || this.$q3.val() === this.$q2.val() || this.$q1.val() === this.$q3.val()) {
	        notify('danger', '问题类型不能重复');
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
	
	new SecurityQuestion({
	  element: '#settings-security-questions-form',
	  saveBtn: '#password-save-btn'
	});

/***/ })
]);