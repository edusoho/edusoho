webpackJsonp(["app/js/course-manage/students/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Students = function () {
	  function Students() {
	    _classCallCheck(this, Students);
	
	    this.initTooltips();
	    this.initDeleteActions();
	    this.initFollowActions();
	    this.initExportActions();
	    this.initExpiryDayActions();
	  }
	
	  _createClass(Students, [{
	    key: 'initTooltips',
	    value: function initTooltips() {
	      $("#refund-coin-tips").popover({
	        html: true,
	        trigger: 'hover', //'hover','click'
	        placement: 'left', //'bottom',
	        content: $("#refund-coin-tips-html").html()
	      });
	    }
	  }, {
	    key: 'initDeleteActions',
	    value: function initDeleteActions() {
	      $('body').on('click', '.js-remove-student', function (evt) {
	        if (!confirm(Translator.trans('course.manage.student_delete_hint'))) {
	          return;
	        }
	        $.post($(evt.target).data('url'), function (data) {
	          if (data.success) {
	            (0, _notify2["default"])('success', Translator.trans('site.delete_success_hint'));
	            location.reload();
	          } else {
	            (0, _notify2["default"])('danger', Translator.trans('site.delete_fail_hint') + ':' + data.message);
	          }
	        });
	      });
	    }
	  }, {
	    key: 'initFollowActions',
	    value: function initFollowActions() {
	      $("#course-student-list").on('click', '.follow-student-btn, .unfollow-student-btn', function () {
	        var $this = $(this);
	        $.post($this.data('url'), function () {
	          $this.hide();
	          if ($this.hasClass('follow-student-btn')) {
	            $this.parent().find('.unfollow-student-btn').show();
	            (0, _notify2["default"])('success', Translator.trans('user.follow_success_hint'));
	          } else {
	            $this.parent().find('.follow-student-btn').show();
	            (0, _notify2["default"])('success', Translator.trans('user.unfollow_success_hint'));
	          }
	        });
	      });
	    }
	  }, {
	    key: 'initExportActions',
	    value: function initExportActions() {
	      var _this = this;
	
	      $('#export-students-btn').on('click', function () {
	        var $exportBtn = $('#export-students-btn');
	        $exportBtn.button('loading');
	        $.get($exportBtn.data('datasUrl'), { start: 0 }, function (response) {
	          if (response.status === 'getData') {
	            _this.exportStudents(response.start, response.fileName);
	          } else {
	            $exportBtn.button('reset');
	            location.href = $exportBtn.data('url') + '?fileName=' + response.fileName;
	          }
	        });
	      });
	    }
	  }, {
	    key: 'initExpiryDayActions',
	    value: function initExpiryDayActions() {
	      $('.js-expiry-days').on('click', function () {
	        (0, _notify2["default"])('danger', '只有按天数设置的学习有效期，才可手动增加有效期。');
	      });
	    }
	  }, {
	    key: 'exportStudents',
	    value: function (_exportStudents) {
	      function exportStudents(_x, _x2) {
	        return _exportStudents.apply(this, arguments);
	      }
	
	      exportStudents.toString = function () {
	        return _exportStudents.toString();
	      };
	
	      return exportStudents;
	    }(function (start, fileName) {
	      var start = start || 0,
	          fileName = fileName || '';
	
	      $.get($('#export-students-btn').data('datasUrl'), { start: start, fileName: fileName }, function (response) {
	        if (response.status === 'getData') {
	          exportStudents(response.start, response.fileName);
	        } else {
	          $('#export-students-btn').button('reset');
	          location.href = $('#export-students-btn').data('url') + '&fileName=' + response.fileName;
	        }
	      });
	    })
	  }]);
	
	  return Students;
	}();
	
	new Students();

/***/ })
]);
//# sourceMappingURL=index.js.map