webpackJsonp(["app/js/task-manage/create/index"],{

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

/***/ }),

/***/ "92bf3ad15db28fd41545":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _loadAnimation = __webpack_require__("b4fbf03f4f16003fe503");
	
	var _loadAnimation2 = _interopRequireDefault(_loadAnimation);
	
	__webpack_require__("b3c50df5d8bf6315aeba");
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _intro = __webpack_require__("423d5c93d4f10f876e3b");
	
	var _intro2 = _interopRequireDefault(_intro);
	
	var _help = __webpack_require__("4e68e437f5b716377a9d");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Editor = function () {
	  function Editor($modal) {
	    _classCallCheck(this, Editor);
	
	    this.$element = $modal;
	    this.$task_manage_content = $('#task-create-content');
	    this.$task_manage_type = $('#task-create-type');
	    this.$frame = null;
	    this.$iframe_body = null;
	    this.iframe_jQuery = null;
	    this.iframe_name = 'task-create-content-iframe';
	    this.mode = this.$task_manage_type.data('editorMode');
	    this.type = this.$task_manage_type.data('editorType');
	    this.step = 1;
	    this.loaded = false;
	    this.contentUrl = '';
	    this._init();
	    this._initEvent();
	  }
	
	  _createClass(Editor, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      $('#course-tasks-submit').click(function (event) {
	        return _this._onSave(event);
	      });
	      $('#course-tasks-next').click(function (event) {
	        return _this._onNext(event);
	      });
	      $('#course-tasks-prev').click(function (event) {
	        return _this._onPrev(event);
	      });
	      if (this.mode != 'edit') {
	        $('.js-course-tasks-item').click(function (event) {
	          return _this._onSetType(event);
	        });
	      } else {
	        $('.delete-task').click(function (event) {
	          return _this._onDelete(event);
	        });
	      }
	    }
	  }, {
	    key: '_init',
	    value: function _init() {
	      this._inItStep1form();
	      this._renderContent(this.step);
	      if (this.mode == 'edit') {
	        this.contentUrl = this.$task_manage_type.data('editorStep2Url');
	        this.step = 2;
	        this._switchPage();
	      }
	    }
	  }, {
	    key: '_onNext',
	    value: function _onNext(e) {
	      if (this.step === 3 || !this._validator(this.step)) {
	        return;
	      }
	      this.step += 1;
	      this._switchPage();
	      this.$element.trigger('afterNext');
	    }
	  }, {
	    key: '_onPrev',
	    value: function _onPrev() {
	      // 第二页可以上一步
	      if (this.step === 1 || this.step == 3 && !this._validator(this.step)) {
	        return;
	      }
	
	      this.step -= 1;
	      this._switchPage();
	    }
	  }, {
	    key: '_onSetType',
	    value: function _onSetType(event) {
	      var $this = $(event.currentTarget).addClass('active');
	      $this.siblings().removeClass('active');
	      var type = $this.data('type');
	      $('[name="mediaType"]').val(type);
	      this.contentUrl = $this.data('contentUrl');
	      this.loaded = this.type === type;
	      this.type = type;
	      this._onNext(event);
	    }
	  }, {
	    key: '_onSave',
	    value: function _onSave(event) {
	      var _this2 = this;
	
	      if (!this._validator(this.step)) {
	        return;
	      }
	
	      $(event.currentTarget).attr('disabled', 'disabled').button('loading');
	      var postData = $('#step1-form').serializeArray().concat(this.$iframe_body.find('#step2-form').serializeArray()).concat(this.$iframe_body.find("#step3-form").serializeArray());
	
	      $.post(this.$task_manage_type.data('saveUrl'), postData).done(function (response) {
	        var needAppend = response.append;
	        var html = response.html;
	        _this2.$element.modal('hide');
	        if (!$('.js-task-empty').hasClass('hidden')) {
	          $('.js-task-empty').addClass('hidden');
	        }
	        if (needAppend === false) {
	          // @TODO这里也需要返回html,进行替换   
	          document.location.reload();
	        }
	
	        var chapterId = postData.find(function (input) {
	          return input.name == 'chapterId';
	        });
	
	        var add = 0;
	        var $parent = $('#' + chapterId.value);
	        var $item = null;
	
	        if ($parent.length) {
	          $parent.nextAll().each(function () {
	            if ($(this).hasClass('task-manage-chapter')) {
	              $(this).before(html);
	              add = 1;
	              (0, _help.sortablelist)('#sortable-list');
	              return false;
	            }
	            if ($parent.hasClass('task-manage-unit') && $(this).hasClass('task-manage-unit')) {
	              $(this).before(html);
	              add = 1;
	              (0, _help.sortablelist)('#sortable-list');
	              return false;
	            }
	          });
	          if (add != 1) {
	            $item = $(html);
	            $("#sortable-list").append($item);
	            add = 1;
	          }
	        } else {
	          $item = $(html);
	          $("#sortable-list").append($item);
	        }
	        _this2.showDefaultSetting($item);
	        _this2.initIntro();
	        (0, _help.sortablelist)('#sortable-list');
	      }).fail(function (response) {
	        var msg = '';
	        var errorResponse = JSON.parse(response.responseText);
	        if (errorResponse.error && errorResponse.error.message) {
	          msg = errorResponse.error.message;
	        }
	        (0, _notify2["default"])('warning', Translator.trans('task_manage.edit_error_hint') + ':' + msg);
	        $("#course-tasks-submit").attr('disabled', null);
	      });
	    }
	  }, {
	    key: 'initIntro',
	    value: function initIntro() {
	      setTimeout(function () {
	        if ($('.js-settings-list').length === 1) {
	          var intro = new _intro2["default"]();
	          intro.initTaskDetailIntro('.js-settings-list');
	        }
	      }, 500);
	    }
	  }, {
	    key: 'showDefaultSetting',
	    value: function showDefaultSetting() {
	      var $item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	
	      if ($item && $item.hasClass('js-task-manage-item')) {
	        $('.js-task-manage-item').removeClass('active').find('.js-settings-list').slideUp();;
	        $item.addClass('active').find('.js-settings-list').slideDown();
	      }
	    }
	  }, {
	    key: '_onDelete',
	    value: function _onDelete(event) {
	      var _this3 = this;
	
	      var $btn = $(event.currentTarget);
	      var url = $btn.data('url');
	      if (url === undefined) {
	        return;
	      }
	      if (!confirm(Translator.trans(Translator.trans('task_manage.delete_hint')))) {
	        return;
	      }
	      $.post(url).then(function (response) {
	        (0, _notify2["default"])('success', Translator.trans('task_manage.delete_success_hint'));
	        _this3.$element.modal('hide');
	
	        document.location.reload();
	      }).fail(function (error) {
	        (0, _notify2["default"])('warning', Translator.trans('task_manage.delete_failed_hint'));
	      });
	    }
	  }, {
	    key: '_switchPage',
	    value: function _switchPage() {
	      this._renderStep(this.step);
	      this._renderContent(this.step);
	      this._rendStepIframe(this.step);
	      this._rendButton(this.step);
	      if (this.step == 2 && !this.loaded) {
	        console.log({ 'loading': new Date().toLocaleTimeString() });
	        this._initIframe();
	      }
	    }
	  }, {
	    key: '_initIframe',
	    value: function _initIframe() {
	      var _this4 = this;
	
	      var html = '<iframe class="' + this.iframe_name + '" id="' + this.iframe_name + '" name="' + this.iframe_name + '" scrolling="no" src="' + this.contentUrl + '"></iframe>';
	      this.$task_manage_content.html(html).show();
	      this.$frame = $('#' + this.iframe_name).iFrameResize();
	      var loadiframe = function loadiframe() {
	        _this4.loaded = true;
	        var validator = {};
	        _this4.iframe_jQuery = _this4.$frame[0].contentWindow.$;
	        _this4.$iframe_body = _this4.$frame.contents().find('body').addClass('task-iframe-body');
	        _this4._rendButton(2);
	        _this4.$iframe_body.find("#step2-form").data('validator', validator);
	        _this4.$iframe_body.find("#step3-form").data('validator', validator);
	        console.log({ 'loaded': new Date().toLocaleTimeString() });
	      };
	      this.$frame.load((0, _loadAnimation2["default"])(loadiframe, this.$task_manage_content));
	    }
	  }, {
	    key: '_inItStep1form',
	    value: function _inItStep1form() {
	      var $step1_form = $("#step1-form");
	      var validator = $step1_form.validate({
	        rules: {
	          mediaType: {
	            required: true
	          }
	        },
	        messages: {
	          mediaType: Translator.trans('validate.choose_item.message')
	        }
	      });
	      $step1_form.data('validator', validator);
	    }
	  }, {
	    key: '_validator',
	    value: function _validator(step) {
	      var validator = null;
	
	      if (step === 1) {
	        validator = $("#step1-form").data('validator');
	      } else if (this.loaded) {
	        var $from = this.$iframe_body.find("#step" + step + "-form");
	        validator = this.iframe_jQuery.data($from[0], 'validator');
	      }
	
	      if (validator && !validator.form()) {
	        return false;
	      }
	      return true;
	    }
	  }, {
	    key: '_rendButton',
	    value: function _rendButton(step) {
	      if (step === 1) {
	        this._renderPrev(false);
	        this._rendSubmit(false);
	        this._renderNext(true);
	      } else if (step === 2) {
	        this._renderPrev(true);
	        if (this.mode === 'edit') {
	          this._renderPrev(false);
	        }
	        if (!this.loaded) {
	          this._rendSubmit(false);
	          this._renderNext(false);
	          return;
	        }
	        this._rendSubmit(true);
	        this._renderNext(true);
	      } else if (step === 3) {
	        this._renderNext(false);
	        this._renderPrev(true);
	      }
	    }
	  }, {
	    key: '_rendStepIframe',
	    value: function _rendStepIframe(step) {
	      if (!this.loaded || !this.$iframe_body) {
	        return;
	      }
	      step === 2 ? this.$iframe_body.find(".js-step2-view").addClass('active') : this.$iframe_body.find(".js-step2-view").removeClass('active');
	      step === 3 ? this.$iframe_body.find(".js-step3-view").addClass('active') : this.$iframe_body.find(".js-step3-view").removeClass('active');
	    }
	  }, {
	    key: '_renderStep',
	    value: function _renderStep(step) {
	      $('#task-create-step').find('li:eq(' + (step - 1) + ')').addClass('doing').prev().addClass('done').removeClass('doing');
	      $('#task-create-step').find('li:eq(' + (step - 1) + ')').next().removeClass('doing').removeClass('done');
	    }
	  }, {
	    key: '_renderContent',
	    value: function _renderContent(step) {
	      step === 1 ? this.$task_manage_type.removeClass('hidden') : this.$task_manage_type.addClass('hidden');
	      step !== 1 ? this.$task_manage_content.removeClass('hidden') : this.$task_manage_content.addClass('hidden');
	    }
	  }, {
	    key: '_renderNext',
	    value: function _renderNext(show) {
	      show ? $("#course-tasks-next").removeClass('hidden').removeAttr("disabled") : $("#course-tasks-next").addClass('hidden');
	    }
	  }, {
	    key: '_renderPrev',
	    value: function _renderPrev(show) {
	      show ? $("#course-tasks-prev").removeClass('hidden') : $("#course-tasks-prev").addClass('hidden');
	    }
	  }, {
	    key: '_rendSubmit',
	    value: function _rendSubmit(show) {
	      show ? $("#course-tasks-submit").removeClass('hidden') : $("#course-tasks-submit").addClass('hidden');
	    }
	  }]);
	
	  return Editor;
	}();
	
	exports["default"] = Editor;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _editor = __webpack_require__("92bf3ad15db28fd41545");
	
	var _editor2 = _interopRequireDefault(_editor);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _editor2["default"]($('#modal'));

/***/ }),

/***/ "b4fbf03f4f16003fe503":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var _arguments = arguments;
	var loadAnimation = function loadAnimation(fn, $element) {
	  var $loading = $('<div class="load-animation"></div>');
	  $loading.prependTo($element).nextAll().hide();
	  $element.append();
	  var arr = [],
	      l = fn.length;
	  return function (x) {
	    arr.push(x);
	    $loading.hide().nextAll().show();
	    /* eslint-disable */
	    return arr.length < l ? _arguments.callee : fn.apply(null, arr);
	    /* eslint-enable */
	  };
	};
	
	exports["default"] = loadAnimation;

/***/ })

});
//# sourceMappingURL=index.js.map