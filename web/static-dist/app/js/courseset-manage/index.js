webpackJsonp(["app/js/courseset-manage/index"],{

/***/ "4e68e437f5b716377a9d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.TaskListHeaderFixed = exports.updateTaskNum = exports.TabChange = exports.showSettings = exports.unpublishTask = exports.publishTask = exports.deleteTask = exports.publishCourse = exports.deleteCourse = exports.closeCourse = exports.taskSortable = exports.sortablelist = undefined;
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _sortable = __webpack_require__("8f840897d9471c8c1fbd");
	
	var _sortable2 = _interopRequireDefault(_sortable);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var sortablelist = exports.sortablelist = function sortablelist(list) {
	  var $list = $(list);
	  var data = $list.sortable("serialize").get();
	
	  var lessonNum = 0,
	      chapterNum = 0,
	      unitNum = 0;
	  $list.find('.task-manage-item').each(function () {
	    var $item = $(this);
	    if ($item.hasClass('js-task-manage-item')) {
	      if ($item.find('.number').length > 0) {
	        lessonNum++;
	        $item.find('.number').text(lessonNum);
	      }
	    } else if ($item.hasClass('task-manage-unit')) {
	      unitNum++;
	      $item.find('.number').text(unitNum);
	    } else if ($item.hasClass('task-manage-chapter')) {
	      chapterNum++;
	      unitNum = 0;
	      $item.find('.number').text(chapterNum);
	    }
	  });
	
	  $list.trigger('finished');
	
	  $.post($list.data('sortUrl'), { ids: data }, function (response) {});
	};
	
	var taskSortable = exports.taskSortable = function taskSortable(list) {
	  if ($(list).length) {
	    (0, _sortable2["default"])({
	      element: list,
	      ajax: false
	    }, function (data) {
	      sortablelist(list);
	    });
	  }
	};
	
	var closeCourse = exports.closeCourse = function closeCourse() {
	  $('body').on('click', '.js-close-course', function (evt) {
	    var $target = $(evt.currentTarget);
	    if (!confirm(Translator.trans('course.manage.close_hint'))) {
	      return;
	    }
	
	    $.post($target.data('check-url'), function (data) {
	
	      if (data.warn) {
	        if (!confirm(Translator.trans(data.message))) {
	          return;
	        }
	      }
	
	      $.post($target.data('url'), function (data) {
	        if (data.success) {
	          (0, _notify2["default"])('success', Translator.trans('course.manage.close_success_hint'));
	          location.reload();
	        } else {
	          (0, _notify2["default"])('danger', Translator.trans('course.manage.close_fail_hint') + ':' + data.message);
	        }
	      });
	    });
	  });
	};
	
	var deleteCourse = exports.deleteCourse = function deleteCourse() {
	  $('body').on('click', '.js-delete-course', function (evt) {
	    if (!confirm(Translator.trans('course.manage.delete_hint'))) {
	      return;
	    }
	    $.post($(evt.currentTarget).data('url'), function (data) {
	      if (data.success) {
	        (0, _notify2["default"])('success', Translator.trans('site.delete_success_hint'));
	        if (data.redirect) {
	          window.location.href = data.redirect;
	        } else {
	          location.reload();
	        }
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('site.delete_fail_hint') + ':' + data.message);
	      }
	    });
	  });
	};
	
	var publishCourse = exports.publishCourse = function publishCourse() {
	  $('body').on('click', '.js-publish-course', function (evt) {
	    if (!confirm(Translator.trans('course.manage.publish_hint'))) {
	      return;
	    }
	    $.post($(evt.target).data('url'), function (data) {
	      if (data.success) {
	        (0, _notify2["default"])('success', Translator.trans('course.manage.task_publish_success_hint'));
	        location.reload();
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('course.manage.task_publish_fail_hint') + ':' + data.message, { delay: 5000 });
	      }
	    });
	  });
	};
	
	var deleteTask = exports.deleteTask = function deleteTask() {
	  $('body').on('click', '.delete-item', function (evt) {
	    if ($(evt.currentTarget).data('type') == 'task') {
	      if (!confirm(Translator.trans('course.manage.task_delete_hint'))) {
	        return;
	      }
	    } else if ($(evt.currentTarget).data('type') == 'chapter') {
	      if (!confirm(Translator.trans('course.manage.chapter_delete_hint'))) {
	        return;
	      }
	    }
	    $.post($(evt.currentTarget).data('url'), function (data) {
	      if (data.success) {
	        (0, _notify2["default"])('success', Translator.trans('site.delete_success_hint'));
	        $(evt.target).parents('.task-manage-item').remove();
	        sortablelist('#sortable-list');
	        console.log($('#sortable-list').children('li').length);
	        if ($('#sortable-list').children('li').length < 1 && $('.js-task-empty').hasClass('hidden')) {
	          $('.js-task-empty').removeClass('hidden');
	        }
	        document.location.reload();
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('site.delete_fail_hint') + ':' + data.message);
	      }
	    });
	  });
	};
	
	var publishTask = exports.publishTask = function publishTask() {
	  $('body').on('click', '.publish-item', function (event) {
	    $.post($(event.target).data('url'), function (data) {
	      if (data.success) {
	        var parentLi = $(event.target).closest('.task-manage-item');
	        (0, _notify2["default"])('success', Translator.trans('course.manage.task_publish_success_hint'));
	        $(parentLi).find('.publish-item').addClass('hidden');
	        $(parentLi).find('.delete-item').addClass('hidden');
	        $(parentLi).find('.unpublish-item').removeClass('hidden');
	        $(parentLi).find('.publish-status').addClass('hidden');
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('course.manage.task_publish_fail_hint') + ':' + data.message);
	      }
	    });
	  });
	};
	
	var unpublishTask = exports.unpublishTask = function unpublishTask() {
	  $('body').on('click', '.unpublish-item', function (event) {
	    $.post($(event.target).data('url'), function (data) {
	      if (data.success) {
	        var parentLi = $(event.target).closest('.task-manage-item');
	        (0, _notify2["default"])('success', Translator.trans('course.manage.task_unpublish_success_hint'));
	        $(parentLi).find('.publish-item').removeClass('hidden');
	        $(parentLi).find('.delete-item').removeClass('hidden');
	        $(parentLi).find('.unpublish-item').addClass('hidden');
	        $(parentLi).find('.publish-status').removeClass('hidden');
	      } else {
	        (0, _notify2["default"])('danger', Translator.trans('course.manage.task_unpublish_fail_hint') + ':' + data.message);
	      }
	    });
	  });
	};
	
	var showSettings = exports.showSettings = function showSettings() {
	  $("#sortable-list").on('click', '.js-item-content', function (event) {
	    console.log('click');
	    var $this = $(event.currentTarget);
	    var $li = $this.closest('.js-task-manage-item');
	    if ($li.hasClass('active')) {
	      $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
	    } else {
	      $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
	      $li.siblings(".js-task-manage-item.active").removeClass('active').find('.js-settings-list').hide();
	    }
	  });
	};
	
	var TabChange = exports.TabChange = function TabChange() {
	  $('[data-role="tab"]').click(function (event) {
	    var $this = $(this);
	    $($this.data('tab-content')).removeClass("hidden").siblings('[data-role="tab-content"]').addClass('hidden');
	  });
	};
	
	var updateTaskNum = exports.updateTaskNum = function updateTaskNum(container) {
	  // let $container = $(container);
	  // $container.on('finished',function(){
	  //   $('#task-num').text($(container).find('i[data-role="task"]').length);
	  // })
	};
	
	var TaskListHeaderFixed = exports.TaskListHeaderFixed = function TaskListHeaderFixed() {
	  var $header = $('.js-task-list-header');
	  if (!$header.length) {
	    return;
	  }
	  var headerTop = $header.offset().top;
	  $(window).scroll(function (event) {
	    if ($(window).scrollTop() >= headerTop) {
	      $header.addClass('fixed');
	    } else {
	      $header.removeClass('fixed');
	    }
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _help = __webpack_require__("4e68e437f5b716377a9d");
	
	var _intro = __webpack_require__("423d5c93d4f10f876e3b");
	
	var _intro2 = _interopRequireDefault(_intro);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	//发布教学计划
	(0, _help.publishCourse)();
	setTimeout(function () {
	  var intro = new _intro2["default"]();
	  intro.introType();
	}, 500);

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