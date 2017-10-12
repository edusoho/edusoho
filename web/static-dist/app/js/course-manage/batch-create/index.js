webpackJsonp(["app/js/course-manage/batch-create/index"],{

/***/ "5899c7c7c1283bfb76ec":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _help = __webpack_require__("4e68e437f5b716377a9d");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var BatchCreate = function () {
	  function BatchCreate(options) {
	    _classCallCheck(this, BatchCreate);
	
	    this.element = $(options.element);
	    this.uploader = null;
	    this.files = [];
	    this.$sortable = $('#sortable-list');
	    this.init();
	  }
	
	  _createClass(BatchCreate, [{
	    key: 'init',
	    value: function init() {
	      this.initUploader();
	      this.initEvent();
	    }
	  }, {
	    key: 'initUploader',
	    value: function initUploader() {
	      var _this = this;
	
	      var $uploader = this.element;
	      this.uploader = new UploaderSDK({
	        id: $uploader.attr('id'),
	        initUrl: $uploader.data('initUrl'),
	        finishUrl: $uploader.data('finishUrl'),
	        accept: $uploader.data('accept'),
	        process: $uploader.data('process'),
	        ui: 'batch',
	        locale: document.documentElement.lang
	      });
	
	      this.uploader.on('file.finish', function (file) {
	        _this.files.push(file);
	      });
	
	      this.uploader.on('error', function (error) {
	        var status = { 'F_DUPLICATE': Translator.trans('uploader.file.exist') };
	        if (!error.message) {
	          error.message = status[error.error];
	        }
	        (0, _notify2["default"])('danger', error.message);
	      });
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this2 = this;
	
	      $('.js-upload-params').on('change', function (event) {
	        _this2.uploader.setProcess(_this2.getUploadProcess());
	      });
	
	      $('.js-batch-create-lesson-btn').on('click', function (event) {
	
	        if (!_this2.files.length) {
	          (0, _notify2["default"])('danger', Translator.trans('uploader.select_one_file'));
	          return;
	        }
	
	        var $btn = $(event.currentTarget);
	        $btn.button('loading');
	        console.log('files', _this2.files);
	
	        _this2.files.map(function (file, index) {
	          var isLast = false;
	          if (index + 1 == _this2.files.length) {
	            isLast = true;
	          }
	          console.log('file', file);
	          _this2.createLesson($btn, file, isLast);
	        });
	      });
	
	      $('[data-toggle="popover"]').popover({
	        html: true
	      });
	    }
	  }, {
	    key: 'getUploadProcess',
	    value: function getUploadProcess() {
	      var uploadProcess = $('.js-upload-params').get().reduce(function (prams, dom) {
	        prams[$(dom).attr('name')] = $(dom).find('option:selected').val();
	        return prams;
	      }, {});
	
	      if ($('[name=support_mobile]').length > 0) {
	        uploadProcess.supportMobile = $('[name=support_mobile]').val();
	      }
	      console.log(uploadProcess);
	      return uploadProcess;
	    }
	  }, {
	    key: 'createLesson',
	    value: function createLesson($btn, file, isLast) {
	      var self = this;
	      $.ajax({
	        type: 'post',
	        url: $btn.data('url'),
	        async: false,
	        data: {
	          fileId: file.id
	        },
	        success: function success(response) {
	          if (response && response.error) {
	            (0, _notify2["default"])('danger', response.error);
	          } else {
	            self.$sortable.append(response.html);
	          }
	        },
	        error: function error(response) {
	          console.log('error', response);
	          (0, _notify2["default"])('danger', Translator.trans('uploader.status.error'));
	        },
	        complete: function complete(response) {
	          console.log('complete', response);
	          if (isLast) {
	            (0, _help.sortablelist)(self.$sortable);
	            $('#modal').modal('hide');
	          }
	        }
	      });
	    }
	  }]);
	
	  return BatchCreate;
	}();
	
	exports["default"] = BatchCreate;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _batchCreate = __webpack_require__("5899c7c7c1283bfb76ec");
	
	var _batchCreate2 = _interopRequireDefault(_batchCreate);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _batchCreate2["default"]({
	  element: '#batch-uploader'
	});

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

/***/ })

});
//# sourceMappingURL=index.js.map