webpackJsonp(["app/js/open-course-manage/lesson-modal/index"],{

/***/ "b00a2728f54f5fed6ab0":
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
	
	var SubtitleDialog = function () {
	  function SubtitleDialog(element) {
	    _classCallCheck(this, SubtitleDialog);
	
	    this.upload_id = 'subtitle-uploader';
	    this.inited = false;
	
	    this.element = $(element);
	
	    if (this.element.length > 0) {
	      this.init();
	      this.inited = true;
	    }
	
	    var $container = this.element.closest('#video-subtitle-form-group');
	    if ($container.find('#ext_mediaId_for_subtitle').val() > 0) {
	      this.render({ id: $container.find('#ext_mediaId_for_subtitle').val() });
	    }
	  }
	
	  _createClass(SubtitleDialog, [{
	    key: 'init',
	    value: function init() {
	
	      var self = this;
	      //删除字幕
	      this.element.on('click', '.js-subtitle-delete', function () {
	        var $elem = $(this);
	        $.post($elem.data('subtitleDeleteUrl'), function (data) {
	          if (data) {
	            (0, _notify2["default"])('success', Translator.trans('activity.video_manage.delete_success_hint'));
	            $elem.parent().remove();
	            $('#' + self.upload_id).show();
	          }
	        });
	      });
	    }
	  }, {
	    key: 'render',
	    value: function render(media) {
	      if (!this.inited) {
	        return;
	      }
	
	      if (media && 'id' in media && media.id > 0) {
	        this.media = media;
	        this.element.html(Translator.trans('activity.video_manage.subtitle_load_hint'));
	        var self = this;
	        $.get(this.element.data('dialogUrl'), { mediaId: this.media.id }, function (html) {
	          self.element.html(html);
	          self.initUploader();
	        });
	      }
	    }
	  }, {
	    key: 'initUploader',
	    value: function initUploader() {
	      var self = this;
	      var $elem = $('#' + this.upload_id);
	      var mediaId = $('.js-subtitle-dialog').data('mediaId');
	      var globalId = $elem.data('mediaGlobalId');
	
	      if (this.uploader) {
	        this._destroyUploader();
	      }
	      var uploader = new UploaderSDK({
	        initUrl: $elem.data('initUrl'),
	        finishUrl: $elem.data('finishUrl'),
	        id: this.upload_id,
	        ui: 'simple',
	        multi: true,
	        accept: {
	          extensions: ['srt'],
	          mimeTypes: ['text/srt']
	        },
	        type: 'sub',
	        process: {
	          videoNo: globalId
	        },
	        locale: document.documentElement.lang
	      });
	
	      uploader.on('error', function (err) {
	        if (err.error === 'Q_TYPE_DENIED') {
	          (0, _notify2["default"])('danger', Translator.trans('activity.video_manage.subtitle_upload_error_hint'));
	        }
	      });
	
	      uploader.on('file.finish', function (file) {
	        $.post($elem.data('subtitleCreateUrl'), {
	          "name": file.name,
	          "subtitleId": file.id,
	          "mediaId": mediaId
	        }).success(function (data) {
	          var convertStatus = {
	            waiting: Translator.trans('activity.video_manage.convert_status_waiting'),
	            doing: Translator.trans('activity.video_manage.convert_status_doing'),
	            success: Translator.trans('activity.video_manage.convert_status_success'),
	            error: Translator.trans('activity.video_manage.convert_status_error'),
	            none: Translator.trans('activity.video_manage.convert_status_none')
	          };
	          $('.js-media-subtitle-list').append('<li class="pvs">' + '<span class="subtitle-name prl">' + data.name + '</span>' + '<span class="subtitle-transcode-status ' + data.convertStatus + '">' + convertStatus[data.convertStatus] + '</span>' + '<a href="javascript:;" class="btn-link pll color-primary js-subtitle-delete" data-subtitle-delete-url="/media/' + mediaId + '/subtitle/' + data.id + '/delete">' + Translator.trans('activity.video_manage.subtitle_delete_hint') + '</a>' + '</li>');
	          if ($('.js-media-subtitle-list li').length > 3) {
	            $('#' + self.upload_id).hide();
	          }
	          (0, _notify2["default"])('success', Translator.trans('activity.video_manage.subtitle_upload_success_hint'));
	        }).error(function (data) {
	          (0, _notify2["default"])('danger', data.responseJSON.error.message);
	        });
	      });
	
	      this.uploader = uploader;
	    }
	  }, {
	    key: 'show',
	    value: function show() {
	      var parent = this.element.parent('.form-group');
	      if (parent.length > 0) {
	        parent.removeClass('hide');
	      }
	    }
	  }, {
	    key: 'hide',
	    value: function hide() {
	      var parent = this.element.parent('.form-group');
	      if (parent.length > 0) {
	        parent.addClass('hide');
	      }
	    }
	  }, {
	    key: '_destroyUploader',
	    value: function _destroyUploader() {
	      if (!this.uploader) {
	        return;
	      }
	      this.uploader.__events = null;
	      try {
	        this.uploader.destroy();
	      } catch (e) {
	        //忽略destroy异常
	      }
	      this.uploader = null;
	    }
	  }, {
	    key: 'destroy',
	    value: function destroy() {
	      if (!this.inited) {
	        return;
	      }
	      this._destroyUploader();
	    }
	  }]);
	
	  return SubtitleDialog;
	}();
	
	exports["default"] = SubtitleDialog;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _lessonModal = __webpack_require__("f7f8f67a4d1dcb4de779");
	
	var _lessonModal2 = _interopRequireDefault(_lessonModal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _lessonModal2["default"]({
	  element: '#modal',
	  form: '#lesson-create-form'
	});

/***/ }),

/***/ "f7f8f67a4d1dcb4de779":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _loadAnimation = __webpack_require__("b4fbf03f4f16003fe503");
	
	var _loadAnimation2 = _interopRequireDefault(_loadAnimation);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	var _dialog = __webpack_require__("b00a2728f54f5fed6ab0");
	
	var _dialog2 = _interopRequireDefault(_dialog);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var LessonModal = function () {
	  function LessonModal(options) {
	    _classCallCheck(this, LessonModal);
	
	    this.$element = $(options.element);
	    this.$form = $(options.form);
	    this.validator();
	    this.initfileChooser();
	  }
	
	  _createClass(LessonModal, [{
	    key: 'validator',
	    value: function validator() {
	      var _this = this;
	
	      var validator = this.$form.validate({
	        currentDom: '#form-submit',
	        ajax: true,
	        groups: {
	          date: 'minute second'
	        },
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          minute: 'required unsigned_integer',
	          second: 'required second_range',
	          'mediaSource': 'required'
	        },
	        messages: {
	          minute: {
	            required: Translator.trans('activity.video_manage.length_required_error_hint')
	          },
	          second: {
	            required: Translator.trans('activity.video_manage.length_required_error_hint'),
	            second_range: Translator.trans('validate.second_range.message')
	          },
	          'mediaSource': Translator.trans('activity.video_manage.media_error_hint')
	        },
	        submitSuccess: function submitSuccess(res) {
	          (0, _notify2["default"])('success', Translator.trans('open_course.lesson.create_success'));
	          document.location.reload();
	        },
	        submitError: function submitError(res) {
	          var msg = '';
	          var errorRes = JSON.parse(res.responseText);
	          if (errorRes.error && errorRes.error.message) {
	            msg = errorRes.error.message;
	          }
	          (0, _notify2["default"])('warning', Translator.trans('open_course.lesson.create_error') + ':' + msg);
	        }
	      });
	
	      $('#form-submit').click(function (event) {
	        if (validator.form()) {
	          _this.$form.submit();
	        }
	      });
	
	      $(".js-length").blur(function () {
	        if (validator && validator.form()) {
	          var minute = parseInt($('#minute').val()) | 0;
	          var second = parseInt($('#second').val()) | 0;
	          $("#length").val(minute * 60 + second);
	        }
	      });
	    }
	  }, {
	    key: 'initfileChooser',
	    value: function initfileChooser() {
	      var fileChooser = new _fileChoose2["default"]();
	      //字幕组件
	      var subtitleDialog = new _dialog2["default"]('.js-subtitle-list');
	      var onSelectFile = function onSelectFile(file) {
	        _fileChoose2["default"].closeUI();
	        if (file.length && file.length > 0) {
	          var minute = parseInt(file.length / 60);
	          var second = Math.round(file.length % 60);
	          $("#minute").val(minute);
	          $("#second").val(second);
	          $("#length").val(minute * 60 + second);
	        }
	        $('#mediaSource').val(file.source);
	        if (file.source == 'self') {
	          $("#mediaId").val(file.id);
	          $("#mediaUri").val('');
	          $("#mediaName").val(file.name);
	        } else {
	          $("#mediaUri").val(file.uri);
	          $("#mediaId").val(0);
	          $("#mediaName").val(file.name);
	        }
	        //渲染字幕
	        subtitleDialog.render(file);
	      };
	
	      this.$element.on('click', '.js-choose-trigger', function (event) {
	        _fileChoose2["default"].openUI();
	        $('[name="mediaSource').val(null);
	      });
	
	      fileChooser.on('select', onSelectFile);
	    }
	  }]);
	
	  return LessonModal;
	}();
	
	exports["default"] = LessonModal;

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