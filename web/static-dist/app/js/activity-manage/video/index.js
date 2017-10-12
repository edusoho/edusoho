webpackJsonp(["app/js/activity-manage/video/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _video = __webpack_require__("8a431cc78fb5d375c291");
	
	var _video2 = _interopRequireDefault(_video);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _video2["default"]();

/***/ }),

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

/***/ "8a431cc78fb5d375c291":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	var _dialog = __webpack_require__("b00a2728f54f5fed6ab0");
	
	var _dialog2 = _interopRequireDefault(_dialog);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Video = function () {
	  function Video() {
	    _classCallCheck(this, Video);
	
	    this.showChooseContent();
	    this.initStep2form();
	    this.isInitStep3from();
	    this.autoValidatorLength();
	    this.initfileChooser();
	    this.hideSubtitleWidget();
	  }
	
	  _createClass(Video, [{
	    key: 'hideSubtitleWidget',
	    value: function hideSubtitleWidget() {
	      var subtitleWidget = $('#video-subtitle-form-group');
	      $('[role="presentation"] a[href!="#import-video-panel"]').click(function () {
	        subtitleWidget.show();
	      });
	      $('a[href="#import-video-panel"]').click(function () {
	        subtitleWidget.hide();
	      });
	    }
	  }, {
	    key: 'showChooseContent',
	    value: function showChooseContent() {
	      $('#iframe-content').on('click', '.js-choose-trigger', function (event) {
	        _fileChoose2["default"].openUI();
	        $('[name="ext[mediaSource]"]').val(null);
	      });
	    }
	  }, {
	    key: 'displayFinishCondition',
	    value: function displayFinishCondition(source) {
	      console.log(source);
	      if (source === 'self') {
	        $("#finish-condition option[value=end]").removeAttr('disabled');
	        $("#finish-condition option[value=end]").text(Translator.trans('activity.video_manage.finish_detail'));
	      } else {
	        $("#finish-condition option[value=end]").text(Translator.trans('activity.video_manage.other_finish_detail'));
	        $("#finish-condition option[value=end]").attr('disabled', 'disabled');
	        $("#finish-condition option[value=time]").attr('selected', false);
	        $("#finish-condition option[value=time]").attr('selected', true);
	        $('.viewLength').removeClass('hidden');
	        this.initStep3from();
	      }
	    }
	  }, {
	    key: 'initStep2form',
	    value: function initStep2form() {
	      var $step2_form = $('#step2-form');
	      var validator = $step2_form.data('validator');
	      $step2_form.validate({
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
	          'ext[mediaSource]': 'required',
	          'ext[finishDetail]': 'unsigned_integer'
	        },
	        messages: {
	          minute: {
	            required: Translator.trans('activity.video_manage.length_required_error_hint')
	          },
	          second: {
	            required: Translator.trans('activity.video_manage.length_unsigned_integer_error_hint'),
	            second_range: Translator.trans('activity.video_manage.second_range_error_hint')
	          },
	          'ext[mediaSource]': Translator.trans("activity.video_manage.media_error_hint")
	        }
	      });
	      $step2_form.data('validator', validator);
	    }
	  }, {
	    key: 'initStep3from',
	    value: function initStep3from() {
	      var $step3_forom = $('#step3-form');
	      var validator = $step3_forom.data('validator');
	      $step3_forom.validate({
	        rules: {
	          'ext[finishDetail]': {
	            required: true,
	            positive_integer: true,
	            max: 300,
	            min: 1
	          }
	        },
	        messages: {
	          'ext[finishDetail]': {
	            required: Translator.trans('activity.video_manage.length_required_error_hint')
	          }
	        }
	      });
	      $step3_forom.data('validator', validator);
	    }
	  }, {
	    key: 'autoValidatorLength',
	    value: function autoValidatorLength() {
	      $(".js-length").blur(function () {
	        var validator = $("#step2-form").data('validator');
	        if (validator && validator.form()) {
	          var minute = parseInt($('#minute').val()) | 0;
	          var second = parseInt($('#second').val()) | 0;
	          $("#length").val(minute * 60 + second);
	        }
	      });
	    }
	  }, {
	    key: 'isInitStep3from',
	    value: function isInitStep3from() {
	      var _this = this;
	
	      // 完成条件是观看时长的情况
	      if ($("#finish-condition").children('option:selected').val() === 'time') {
	        $('.viewLength').removeClass('hidden');
	        this.initStep3from();
	      }
	
	      $("#finish-condition").on('change', function (event) {
	        if (event.target.value == 'time') {
	          $('.viewLength').removeClass('hidden');
	          _this.initStep3from();
	        } else {
	          $('.viewLength').addClass('hidden');
	          $('input[name="ext[finishDetail]"]').rules('remove');
	        }
	      });
	    }
	  }, {
	    key: 'initfileChooser',
	    value: function initfileChooser() {
	      var _this2 = this;
	
	      var fileChooser = new _fileChoose2["default"]();
	      //字幕组件
	      var subtitleDialog = new _dialog2["default"]('.js-subtitle-list');
	      var onSelectFile = function onSelectFile(file) {
	        _this2.displayFinishCondition(file.source);
	        _fileChoose2["default"].closeUI();
	
	        var placeMediaAttr = function placeMediaAttr(file) {
	          if (file.length !== 0 && file.length !== undefined) {
	            var $minute = $('#minute');
	            var $second = $('#second');
	            var $length = $('#length');
	
	            var length = parseInt(file.length);
	            var minute = parseInt(length / 60);
	            var second = length % 60;
	            $minute.val(minute);
	            $second.val(second);
	            $length.val(length);
	            file.minute = minute;
	            file.second = second;
	          }
	          $('[name="media"]').val(JSON.stringify(file));
	        };
	        placeMediaAttr(file);
	
	        $('[name="ext[mediaSource]"]').val(file.source);
	        $("#step2-form").valid();
	        if (file.source == 'self') {
	          $("#ext_mediaId").val(file.id);
	          $("#ext_mediaUri").val('');
	        } else {
	          $("#ext_mediaUri").val(file.uri);
	          $("#ext_mediaId").val(0);
	        }
	        //渲染字幕
	        subtitleDialog.render(file);
	      };
	
	      fileChooser.on('select', onSelectFile);
	    }
	  }]);
	
	  return Video;
	}();
	
	exports["default"] = Video;

/***/ })

});
//# sourceMappingURL=index.js.map