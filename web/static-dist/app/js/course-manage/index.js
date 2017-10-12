webpackJsonp(["app/js/course-manage/index"],{

/***/ "d14d05cad9e7abf02a5d":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var toggleIcon = exports.toggleIcon = function toggleIcon(target, $expandIconClass, $putIconClass) {
	  var $icon = target.find('.js-remove-icon');
	  var $text = target.find('.js-remove-text');
	  if ($icon.hasClass($expandIconClass)) {
	    $icon.removeClass($expandIconClass).addClass($putIconClass);
	    $text ? $text.text(Translator.trans('收起')) : '';
	  } else {
	    $icon.removeClass($putIconClass).addClass($expandIconClass);
	    $text ? $text.text(Translator.trans('展开')) : '';
	  }
	};
	
	var chapterAnimate = exports.chapterAnimate = function chapterAnimate() {
	  var delegateTarget = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
	  var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.js-task-chapter';
	  var $expandIconClass = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'es-icon-remove';
	  var $putIconClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'es-icon-anonymous-iconfont';
	
	  $(delegateTarget).on('click', target, function (event) {
	    var $this = $(event.currentTarget);
	    $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, "normal");
	    toggleIcon($this, $expandIconClass, $putIconClass);
	  });
	};

/***/ }),

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
	
	var _chapterAnimate = __webpack_require__("d14d05cad9e7abf02a5d");
	
	$('[data-help="popover"]').popover();
	
	var sortableList = '#sortable-list';
	(0, _help.taskSortable)(sortableList);
	(0, _help.updateTaskNum)(sortableList);
	(0, _help.closeCourse)();
	(0, _help.deleteCourse)();
	(0, _help.deleteTask)();
	(0, _help.publishTask)();
	(0, _help.unpublishTask)();
	(0, _help.showSettings)();
	(0, _help.TaskListHeaderFixed)();
	// @TODO拆分，这个js被几个页面引用了有的页面根本不用js
	
	$('#sortable-list').on('click', '.js-chapter-toggle-show', function (event) {
	  var $this = $(event.currentTarget);
	  var $chapter = $this.closest('.js-task-manage-chapter');
	  $chapter.nextUntil('.js-task-manage-chapter').animate({ height: 'toggle', opacity: 'toggle' }, "normal");
	  (0, _chapterAnimate.toggleIcon)($chapter, 'es-icon-keyboardarrowdown', 'es-icon-keyboardarrowup');
	});

/***/ })

});
//# sourceMappingURL=index.js.map