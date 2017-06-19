webpackJsonp(["app/js/course-manage/live-replay/upload/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	var fileChooser = new _fileChoose2.default();
	var $fileId = $('#material-file-chooser').find('[name=fileId]');
	fileChooser.on('select', function (file) {
	  $fileId.val(file.id);
	  _fileChoose2.default.closeUI();
	  $('.jq-validate-error').remove();
	});
	
	$('.js-choose-trigger').click(function (event) {
	  _fileChoose2.default.openUI();
	  $fileId.val('');
	});
	
	var $form = $('#replay-material-form');
	
	$form.validate({
	  rules: {
	    fileId: {
	      required: true
	    }
	  },
	  messages: {
	    fileId: '请上传录像文件'
	  }
	});

/***/ }),

/***/ "eca7a2561fa47d3f75f6":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	import MaterialLibChoose from './base/materiallib-choose';
	import VideoImport from './base/import-video';
	import CourseFileChoose from './base/coursefile-choose';
	import UploadChooser from './base/upload-chooser';
	import Emitter from "component-emitter";
	
	var FileChooser = function (_Emitter) {
	  _inherits(FileChooser, _Emitter);
	
	  function FileChooser(options) {
	    _classCallCheck(this, FileChooser);
	
	    var _this = _possibleConstructorReturn(this, (FileChooser.__proto__ || Object.getPrototypeOf(FileChooser)).call(this));
	
	    _this.init();
	    return _this;
	  }
	
	  _createClass(FileChooser, [{
	    key: 'init',
	    value: function init() {
	      this.initTab();
	      this.initFileChooser();
	    }
	  }, {
	    key: 'initTab',
	    value: function initTab() {
	      $("#material a").click(function (e) {
	        e.preventDefault();
	        var $this = $(this);
	        $this.find('[type="radio"]').prop('checked', 'checked');
	        $this.closest('li').siblings('li').find('[type="radio"]').prop('checked', false);
	        $this.tab('show');
	      });
	
	      if ($('.js-import-video').data('link')) {
	        $('.js-import-video').click();
	      }
	    }
	  }, {
	    key: 'initFileChooser',
	    value: function initFileChooser() {
	      var _this2 = this;
	
	      var materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
	      var courseFileChoose = new CourseFileChoose($('#chooser-course-panel'));
	      var videoImport = new VideoImport($('#import-video-panel'));
	      var uploader = new UploadChooser($('#chooser-upload-panel'));
	      materialLibChoose.on('select', function (file) {
	        return _this2.fileSelect(file);
	      });
	      courseFileChoose.on('select', function (file) {
	        return _this2.fileSelect(file);
	      });
	      videoImport.on('file.select', function (file) {
	        return _this2.fileSelect(file);
	      });
	      uploader.on('select', function (file) {
	        return _this2.fileSelect(file);
	      });
	    }
	  }, {
	    key: 'fileSelect',
	    value: function fileSelect(file) {
	      this.fillTitle(file);
	      this.emit('select', file);
	    }
	  }, {
	    key: 'fillTitle',
	    value: function fillTitle(file) {
	      var $title = $("#title");
	      if ($title.length > 0 && $title.val() == '') {
	        var title = file.name.substring(0, file.name.lastIndexOf('.'));
	        $title.val(title);
	      }
	    }
	  }], [{
	    key: 'openUI',
	    value: function openUI() {
	      $('.file-chooser-bar').addClass('hidden');
	      $('.file-chooser-main').removeClass('hidden');
	    }
	  }, {
	    key: 'closeUI',
	    value: function closeUI() {
	      $('.file-chooser-main').addClass('hidden');
	      $('.file-chooser-bar').removeClass('hidden');
	    }
	  }]);
	
	  return FileChooser;
	}(Emitter);
	
	export default FileChooser;

/***/ })

});