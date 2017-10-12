webpackJsonp(["app/js/course-manage/create/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _intro = __webpack_require__("423d5c93d4f10f876e3b");
	
	var _intro2 = _interopRequireDefault(_intro);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Creator = function () {
	  function Creator() {
	    _classCallCheck(this, Creator);
	
	    this.validator = null;
	    this.init();
	    // this.isInitIntro();
	  }
	
	  _createClass(Creator, [{
	    key: 'init',
	    value: function init() {
	      $('[data-toggle="popover"]').popover({
	        html: true
	      });
	      this.initValidator();
	      this.initExpiryMode();
	      this.checkBoxChange();
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var _this = this;
	
	      var $form = $("#course-create-form");
	      this.validator = $form.validate({
	        groups: {
	          date: 'expiryStartDate expiryEndDate'
	        },
	        rules: {
	          title: {
	            required: true,
	            trim: true
	          }
	        },
	        messages: {
	          title: Translator.trans('course.manage.title_required_error_hint')
	        }
	      });
	
	      $('#course-submit').click(function (evt) {
	        if (_this.validator.form()) {
	          _this.isInitIntro();
	          $(evt.currentTarget).button('loading');
	          $form.submit();
	        }
	      });
	      this.initDatePicker('#expiryStartDate');
	      this.initDatePicker('#expiryEndDate');
	      this.initDatePicker('#deadline');
	    }
	  }, {
	    key: 'isInitIntro',
	    value: function isInitIntro() {
	      var listLength = $('#courses-list-table').find('tbody tr').length;
	      if (listLength == 1) {
	        var intro = new _intro2["default"]();
	        intro.isSetCourseListCookies();
	      }
	    }
	  }, {
	    key: 'checkBoxChange',
	    value: function checkBoxChange() {
	      var _this2 = this;
	
	      $('input[name="deadlineType"]').on('change', function (event) {
	        if ($('input[name="deadlineType"]:checked').val() == 'end_date') {
	          $('#deadlineType-date').removeClass('hidden');
	          $('#deadlineType-days').addClass('hidden');
	        } else {
	          $('#deadlineType-date').addClass('hidden');
	          $('#deadlineType-days').removeClass('hidden');
	        }
	        _this2.initExpiryMode();
	      });
	
	      $('input[name="expiryMode"]').on('change', function (event) {
	        if ($('input[name="expiryMode"]:checked').val() == 'date') {
	          $('#expiry-days').removeClass('hidden').addClass('hidden');
	          $('#expiry-date').removeClass('hidden');
	        } else if ($('input[name="expiryMode"]:checked').val() == 'days') {
	          $('#expiry-date').removeClass('hidden').addClass('hidden');
	          $('#expiry-days').removeClass('hidden');
	          $('input[name="deadlineType"][value="days"]').prop('checked', true);
	        } else {
	          $('#expiry-date').removeClass('hidden').addClass('hidden');
	          $('#expiry-days').removeClass('hidden').addClass('hidden');
	        }
	        _this2.initExpiryMode();
	      });
	
	      $('input[name="learnMode"]').on('change', function (event) {
	        if ($('input[name="learnMode"]:checked').val() == 'freeMode') {
	          $('#learnLockModeHelp').removeClass('hidden').addClass('hidden');
	          $('#learnFreeModeHelp').removeClass('hidden');
	        } else {
	          $('#learnFreeModeHelp').removeClass('hidden').addClass('hidden');
	          $('#learnLockModeHelp').removeClass('hidden');
	        }
	      });
	    }
	  }, {
	    key: 'initDatePicker',
	    value: function initDatePicker($id) {
	      var _this3 = this;
	
	      var $picker = $($id);
	      $picker.datetimepicker({
	        format: 'yyyy-mm-dd',
	        language: document.documentElement.lang,
	        minView: 2, //month
	        autoclose: true,
	        endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
	      }).on('hide', function () {
	        _this3.validator.form();
	      });
	      $picker.datetimepicker('setStartDate', new Date());
	    }
	  }, {
	    key: 'initExpiryMode',
	    value: function initExpiryMode() {
	      var $deadline = $('[name="deadline"]');
	      var $expiryDays = $('[name="expiryDays"]');
	      var $expiryStartDate = $('[name="expiryStartDate"]');
	      var $expiryEndDate = $('[name="expiryEndDate"]');
	      var expiryMode = $('[name="expiryMode"]:checked').val();
	
	      this.elementRemoveRules($deadline);
	      this.elementRemoveRules($expiryDays);
	      this.elementRemoveRules($expiryStartDate);
	      this.elementRemoveRules($expiryEndDate);
	
	      switch (expiryMode) {
	        case 'days':
	          var $deadlineType = $('[name="deadlineType"]:checked');
	          if ($deadlineType.val() === 'end_date') {
	            this.elementAddRules($deadline, this.getDeadlineEndDateRules());
	            this.validator.form();
	            return;
	          }
	          this.elementAddRules($expiryDays, this.getExpiryDaysRules());
	          this.validator.form();
	          break;
	        case 'date':
	          this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
	          this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
	          this.validator.form();
	          break;
	        default:
	          break;
	      }
	    }
	  }, {
	    key: 'getExpiryEndDateRules',
	    value: function getExpiryEndDateRules() {
	      return {
	        required: true,
	        date: true,
	        after_date: '#expiryStartDate',
	        messages: {
	          required: Translator.trans('course.manage.expiry_end_date_error_hint')
	        }
	      };
	    }
	  }, {
	    key: 'getExpiryStartDateRules',
	    value: function getExpiryStartDateRules() {
	      return {
	        required: true,
	        date: true,
	        after_now_date: true,
	        before_date: '#expiryEndDate',
	        messages: {
	          required: Translator.trans('course.manage.expiry_start_date_error_hint')
	        }
	      };
	    }
	  }, {
	    key: 'getExpiryDaysRules',
	    value: function getExpiryDaysRules() {
	      return {
	        required: true,
	        positive_integer: true,
	        max_year: true,
	        messages: {
	          required: Translator.trans('course.manage.expiry_days_error_hint')
	        }
	      };
	    }
	  }, {
	    key: 'getDeadlineEndDateRules',
	    value: function getDeadlineEndDateRules() {
	      return {
	        required: true,
	        date: true,
	        after_now_date: true,
	        messages: {
	          required: Translator.trans('course.manage.deadline_end_date_error_hint')
	        }
	      };
	    }
	  }, {
	    key: 'elementAddRules',
	    value: function elementAddRules($element, options) {
	      $element.rules("add", options);
	    }
	  }, {
	    key: 'elementRemoveRules',
	    value: function elementRemoveRules($element) {
	      $element.rules('remove');
	    }
	  }]);
	
	  return Creator;
	}();
	
	new Creator();

/***/ }),

/***/ "423d5c93d4f10f876e3b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("d5e8fa5f17ac5fe79c78");
	
	var _jsCookie = __webpack_require__("fe53252afd7b6c35cb73");
	
	var _jsCookie2 = _interopRequireDefault(_jsCookie);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var COURSE_BASE_INTRO = 'COURSE_BASE_INTRO';
	var COURSE_TASK_INTRO = 'COURSE_TASK_INTRO';
	var COURSE_TASK_DETAIL_INTRO = 'COURSE_TASK_DETAIL_INTRO';
	var COURSE_LIST_INTRO = 'COURSE_LIST_INTRO';
	var COURSE_LIST_INTRO_COOKIE = 'COURSE_LIST_INTRO_COOKIE';
	
	var Intro = function () {
	  function Intro() {
	    var _this = this;
	
	    _classCallCheck(this, Intro);
	
	    this.intro = null;
	    this.customClass = "es-intro-help multistep";
	    $('body').on('click', '.js-skip', function (event) {
	      _this.intro.exit();
	    });
	  }
	
	  _createClass(Intro, [{
	    key: 'introType',
	    value: function introType() {
	      if (this.isTaskCreatePage()) {
	        this.initTaskCreatePageIntro();
	        return;
	      }
	      if (!this.isCourseListPage()) {
	        this.initNotTaskCreatePageIntro();
	        return;
	      }
	      this.initCourseListPageIntro();
	    }
	  }, {
	    key: 'isCourseListPage',
	    value: function isCourseListPage() {
	      return !!$('#courses-list-table').length;
	    }
	  }, {
	    key: 'isTaskCreatePage',
	    value: function isTaskCreatePage() {
	      return !!$('#step-3').length;
	    }
	  }, {
	    key: 'isInitTaskDetailIntro',
	    value: function isInitTaskDetailIntro() {
	      $('.js-task-manage-item').attr('into-step-id', 'step-5');
	      return !!$('.js-settings-list').length;
	    }
	  }, {
	    key: 'introStart',
	    value: function introStart(steps) {
	      var _this2 = this;
	
	      var doneLabel = '<i class="es-icon es-icon-close01"></i>';
	      this.intro = introJs();
	      if (steps.length < 2) {
	        doneLabel = Translator.trans('intro.confirm_hint');
	        this.customClass = "es-intro-help";
	      } else {
	        this.customClass = "es-intro-help multistep";
	      }
	      console.log(steps.length < 2);
	      console.log(this.customClass);
	      console.log(doneLabel);
	      this.intro.setOptions({
	        steps: steps,
	        skipLabel: doneLabel,
	        nextLabel: Translator.trans('course_set.manage.next_label'),
	        prevLabel: Translator.trans('course_set.manage.prev_label'),
	        doneLabel: doneLabel,
	        showBullets: false,
	        tooltipPosition: 'auto',
	        showStepNumbers: false,
	        exitOnEsc: false,
	        exitOnOverlayClick: false,
	        tooltipClass: this.customClass
	      });
	
	      this.intro.start().onexit(function () {}).onchange(function () {
	        console.log(_this2.intro);
	        if (_this2.intro._currentStep == _this2.intro._introItems.length - 1) {
	          $('.introjs-nextbutton').before('<a class="introjs-button  done-button js-skip">' + Translator.trans('intro.confirm_hint') + '<a/>');
	        } else {
	          $('.js-skip').remove();
	        }
	      });
	    }
	  }, {
	    key: 'initTaskCreatePageIntro',
	    value: function initTaskCreatePageIntro() {
	      $('.js-task-manage-item:first .js-item-content').trigger('click');
	      if (!store.get(COURSE_BASE_INTRO) && !store.get(COURSE_TASK_INTRO)) {
	        store.set(COURSE_BASE_INTRO, true);
	        store.set(COURSE_TASK_INTRO, true);
	        this.introStart(this.initAllSteps());
	      } else if (!store.get(COURSE_TASK_INTRO)) {
	        store.set(COURSE_TASK_INTRO, true);
	        this.introStart(this.initTaskSteps());
	      }
	    }
	  }, {
	    key: 'initTaskDetailIntro',
	    value: function initTaskDetailIntro(element) {
	      if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
	        store.set(COURSE_TASK_DETAIL_INTRO, true);
	        this.introStart(this.initTaskDetailSteps(element));
	      }
	    }
	  }, {
	    key: 'initNotTaskCreatePageIntro',
	    value: function initNotTaskCreatePageIntro() {
	      if (!store.get(COURSE_BASE_INTRO)) {
	        store.set(COURSE_BASE_INTRO, true);
	        this.introStart(this.initNotTaskPageSteps());
	      }
	    }
	  }, {
	    key: 'isSetCourseListCookies',
	    value: function isSetCourseListCookies() {
	      if (!store.get(COURSE_LIST_INTRO)) {
	        _jsCookie2["default"].set(COURSE_LIST_INTRO_COOKIE, true);
	      }
	    }
	  }, {
	    key: 'initCourseListPageIntro',
	    value: function initCourseListPageIntro() {
	      var _this3 = this;
	
	      var listLength = $('#courses-list-table').find('tbody tr').length;
	
	      if (!(listLength === 2) || store.get(COURSE_LIST_INTRO) || !_jsCookie2["default"].get(COURSE_LIST_INTRO_COOKIE)) {
	        return;
	      }
	      _jsCookie2["default"].remove(COURSE_LIST_INTRO_COOKIE);
	      new Promise(function (resolve, reject) {
	        setTimeout(function () {
	          var $courseMenu = $('.js-sidenav-course-menu');
	          if (!$courseMenu.length) {
	            resolve();
	            return;
	          }
	          $('.js-sidenav-course-menu').slideUp(function () {
	            resolve();
	          });
	        }, 100);
	      }).then(function () {
	        setTimeout(function () {
	          _this3.initCourseListIntro('.js-sidenav');
	          console.log('initCourseListIntro');
	        }, 100);
	      });
	    }
	  }, {
	    key: 'initCourseListIntro',
	    value: function initCourseListIntro(element) {
	      if (!store.get(COURSE_LIST_INTRO)) {
	        store.set(COURSE_LIST_INTRO, true);
	        this.introStart(this.initCourseListSteps(element));
	      }
	    }
	  }, {
	    key: 'initAllSteps',
	    value: function initAllSteps() {
	      var arry = [{
	        intro: Translator.trans('course_set.manage.upgrade_hint')
	      }, {
	        element: '#step-1',
	        intro: Translator.trans('course_set.manage.upgrade_step1_hint')
	      }, {
	        element: '#step-2',
	        intro: Translator.trans('course_set.manage.upgrade_step2_hint')
	      }, {
	        element: '#step-3',
	        intro: Translator.trans('course_set.manage.upgrade_step3_hint')
	      }];
	      //如果存在任务
	      if (this.isInitTaskDetailIntro()) {
	        arry.push({
	          element: '[into-step-id="step-5"]',
	          intro: Translator.trans('course_set.manage.upgrade_step5_hint')
	        });
	        if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
	          store.set(COURSE_TASK_DETAIL_INTRO, true);
	        }
	      }
	      return arry;
	    }
	  }, {
	    key: 'initNotTaskPageSteps',
	    value: function initNotTaskPageSteps() {
	      return [{
	        intro: Translator.trans('course_set.manage.upgrade_hint')
	      }, {
	        element: '#step-1',
	        intro: Translator.trans('course_set.manage.upgrade_step1_hint')
	      }, {
	        element: '#step-2',
	        intro: Translator.trans('course_set.manage.upgrade_step2_hint')
	      }];
	    }
	  }, {
	    key: 'initTaskSteps',
	    value: function initTaskSteps() {
	      var arry = [{
	        element: '#step-3',
	        intro: Translator.trans('course_set.manage.upgrade_step3_hint')
	      }];
	      //如果存在任务
	      if (this.isInitTaskDetailIntro()) {
	        arry.push({
	          element: '#step-5',
	          intro: Translator.trans('course_set.manage.upgrade_step5_hint'),
	          position: 'bottom'
	        });
	        if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
	          store.set(COURSE_TASK_DETAIL_INTRO, true);
	        }
	      }
	
	      return arry;
	    }
	  }, {
	    key: 'initTaskDetailSteps',
	    value: function initTaskDetailSteps(element) {
	      return [{
	        element: element,
	        intro: Translator.trans('course_set.manage.activity_link_hint'),
	        position: 'bottom'
	      }];
	    }
	  }, {
	    key: 'initCourseListSteps',
	    value: function initCourseListSteps(element) {
	      return [{
	        element: element,
	        intro: Translator.trans('course_set.manage.hint')
	      }];
	    }
	  }, {
	    key: 'initResetStep',
	    value: function initResetStep() {
	      var introBtnClassName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	
	      return [{
	        element: '.js-intro-btn-group',
	        intro: Translator.trans('course_set.manage.all_tutorial', { 'introBtnClassName': introBtnClassName }),
	        position: 'top'
	      }];
	    }
	  }]);
	
	  return Intro;
	}();
	
	exports["default"] = Intro;

/***/ })

});
//# sourceMappingURL=index.js.map