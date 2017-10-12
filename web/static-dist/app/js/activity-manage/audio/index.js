webpackJsonp(["app/js/activity-manage/audio/index"],{

/***/ "8533a2a15206a0ac5cb6":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	var _chooserUi = __webpack_require__("f324dbdea53170d5000f");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Audio = function () {
	  function Audio() {
	    _classCallCheck(this, Audio);
	
	    (0, _chooserUi.showChooserType)($('[name="ext[mediaId]"]'));
	    this.initStep2Form();
	    this.autoValidatorLength();
	    this.initFileChooser();
	  }
	
	  _createClass(Audio, [{
	    key: 'initStep2Form',
	    value: function initStep2Form() {
	      var $step2_form = $('#step2-form');
	      var validator = $step2_form.data('validator');
	
	      $step2_form.validate({
	        groups: {
	          nameGroup: 'minute second'
	        },
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          minute: 'required unsigned_integer unsigned_integer',
	          second: 'required second_range unsigned_integer',
	          'ext[mediaId]': 'required'
	        },
	        messages: {
	          minute: {
	            required: Translator.trans('activity.audio_manage.length_required_error_hint'),
	            unsigned_integer: Translator.trans('activity.audio_manage.length_unsigned_integer_error_hint')
	          },
	          second: {
	            required: Translator.trans('activity.audio_manage.length_required_error_hint'),
	            second_range: Translator.trans('activity.audio_manage.second_range_error_hint'),
	            unsigned_integer: Translator.trans('activity.audio_manage.length_unsigned_integer_error_hint')
	          },
	          'ext[mediaId]': Translator.trans('activity.audio_manage.media_error_hint')
	        }
	      });
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
	    key: 'initFileChooser',
	    value: function initFileChooser() {
	      var fileChooser = new _fileChoose2["default"]();
	      console.log(fileChooser);
	      var onSelectFile = function onSelectFile(file) {
	        (0, _chooserUi.chooserUiClose)();
	        var placeMediaAttr = function placeMediaAttr(file) {
	          if (file.length !== 0 && file.length !== undefined) {
	            var $minute = $('#minute');
	            var $second = $('#second');
	
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
	
	        $('[name="ext[mediaId]"]').val(file.source);
	        $("#step2-form").valid();
	        if (file.source == 'self') {
	          $("#ext_mediaId").val(file.id);
	          $("#ext_mediaUri").val('');
	        } else {
	          $("#ext_mediaId").val('');
	          $("#ext_mediaUri").val(file.uri);
	        }
	      };
	      fileChooser.on('select', onSelectFile);
	    }
	  }]);
	
	  return Audio;
	}();
	
	exports["default"] = Audio;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _audio = __webpack_require__("8533a2a15206a0ac5cb6");
	
	var _audio2 = _interopRequireDefault(_audio);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _audio2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map