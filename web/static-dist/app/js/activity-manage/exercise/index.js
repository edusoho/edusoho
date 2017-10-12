webpackJsonp(["app/js/activity-manage/exercise/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _selectLinkage = __webpack_require__("1be2a74362f00ba903a0");
	
	var _selectLinkage2 = _interopRequireDefault(_selectLinkage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Exercise = function () {
	  function Exercise($form) {
	    _classCallCheck(this, Exercise);
	
	    this.$element = $form;
	    this.validator2 = null;
	    this._setValidateRule();
	    this._init();
	    this._initEvent();
	  }
	
	  _createClass(Exercise, [{
	    key: '_init',
	    value: function _init() {
	      this._inItStep2form();
	      this.fix();
	    }
	  }, {
	    key: '_initEvent',
	    value: function _initEvent() {}
	  }, {
	    key: '_inItStep2form',
	    value: function _inItStep2form() {
	      var $step2_form = $("#step2-form");
	
	      this.validator2 = $step2_form.validate({
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          itemCount: {
	            required: true,
	            positiveInteger: true,
	            min: 1,
	            max: 9999
	          },
	          range: {
	            required: true
	          },
	          difficulty: {
	            required: true
	          },
	          'questionTypes[]': {
	            required: true,
	            remote: {
	              url: $('[name="checkQuestion"]').data('checkUrl'),
	              type: "post",
	              dataType: "json",
	              async: false,
	              data: {
	                itemCount: function itemCount() {
	                  return $('[name="itemCount"]').val();
	                },
	                range: function range() {
	                  var range = {};
	                  var courseId = $('[name="range[courseId]"]').val();
	                  range.courseId = courseId;
	                  if ($('[name="range[lessonId]"]').length > 0) {
	                    var lessonId = $('[name="range[lessonId]"]').val();
	                    range.lessonId = lessonId;
	                  }
	
	                  return JSON.stringify(range);
	                },
	                difficulty: function difficulty() {
	                  return $('[name="difficulty"]').val();
	                },
	                types: function types() {
	                  var types = [];
	                  $('[name="questionTypes\[\]"]:checked').each(function () {
	                    types.push($(this).val());
	                  });
	                  return types;
	                }
	              }
	            }
	          }
	        },
	        messages: {
	          required: Translator.trans("activity.exercise_manage.title_required_error_hint"),
	          range: Translator.trans("activity.exercise_manage.title_range_error_hint"),
	          itemCount: {
	            required: Translator.trans('activity.exercise_manage.item_count_required_error_hint'),
	            positiveInteger: Translator.trans('activity.exercise_manage.item_count_positive_integer_error_hint'),
	            min: Translator.trans('activity.exercise_manage.item_count_min_error_hint'),
	            max: Translator.trans('activity.exercise_manage.item_count_max_error_hint')
	          },
	          difficulty: Translator.trans("activity.exercise_manage.difficulty_required_error_hint"),
	          'questionTypes[]': {
	            required: Translator.trans("activity.exercise_manage.question_required_error_hint"),
	            remote: Translator.trans("activity.exercise_manage.question_remote_error_hint")
	          }
	        }
	      });
	
	      $step2_form.data('validator', this.validator2);
	    }
	  }, {
	    key: '_inItStep3form',
	    value: function _inItStep3form() {
	      var $step3_form = $("#step3-form");
	      var validator = $step3_form.validate({
	        onkeyup: false,
	        rules: {
	          finishCondition: {
	            required: true
	          }
	        },
	        messages: {
	          finishCondition: Translator.trans("activity.exercise_manage.finish_detail_required_error_hint")
	        }
	      });
	      $step3_form.data('validator', validator);
	    }
	  }, {
	    key: '_setValidateRule',
	    value: function _setValidateRule() {
	      $.validator.addMethod("positiveInteger", function (value, element) {
	        return this.optional(element) || /^[1-9]\d*$/.test(value);
	      }, $.validator.format(Translator.trans("activity.exercise_manage.item_count_positive_integer_error_hint")));
	    }
	  }, {
	    key: 'fix',
	    value: function fix() {
	      var _this = this;
	
	      $('.js-question-type').click(function () {
	        _this.validator2.form();
	      });
	    }
	  }]);
	
	  return Exercise;
	}();
	
	new Exercise($('#step2-form'));
	new _selectLinkage2["default"]($('[name="range[courseId]"]'), $('[name="range[lessonId]"]'));
	
	checkQuestionNum();
	
	$('[name="range[courseId]"]').change(function () {
	  checkQuestionNum();
	});
	
	$('[name="range[lessonId]"]').change(function () {
	  checkQuestionNum();
	});
	
	$('[name="difficulty"]').change(function () {
	  checkQuestionNum();
	});
	
	function checkQuestionNum() {
	  var url = $('[name="range[courseId]"]').data('checkNumUrl');
	  var courseId = $('[name="range[courseId]"]').val();
	  var lessonId = $('[name="range[lessonId]"]').val();
	  var difficulty = $('[name="difficulty"]').val();
	
	  $.post(url, { courseId: courseId, lessonId: lessonId, difficulty: difficulty }, function (data) {
	    $('[role="questionNum"]').text(0);
	
	    $.each(data, function (i, n) {
	      $("[type='" + i + "']").text(n.questionNum);
	    });
	  });
	}

/***/ })
]);
//# sourceMappingURL=index.js.map