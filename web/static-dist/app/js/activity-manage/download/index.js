webpackJsonp(["app/js/activity-manage/download/index"],{

/***/ "fbf0b6283b62b602eb6b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _chooserUi = __webpack_require__("f324dbdea53170d5000f");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DownLoad = function () {
	  function DownLoad() {
	    _classCallCheck(this, DownLoad);
	
	    this.$form = $('#step2-form');
	    this.validator2 = null;
	    this.firstName = $('#title').val();
	    this.initStep2Form();
	    this.bindEvent();
	    this.initFileChooser();
	  }
	
	  _createClass(DownLoad, [{
	    key: 'initStep2Form',
	    value: function initStep2Form() {
	      var validator2 = this.$form.validate({
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          link: 'url',
	          materials: 'required'
	        },
	        messages: {
	          link: Translator.trans("activity.download_manage.link_error_hint"),
	          materials: Translator.trans('activity.download_manage.materials_error_hint')
	        }
	      });
	      this.$form.data('validator', validator2);
	    }
	  }, {
	    key: 'bindEvent',
	    value: function bindEvent() {
	      var _this = this;
	
	      this.$form.on('click', '.js-btn-delete', function (event) {
	        return _this.itemDelete(event);
	      });
	      this.$form.on('click', '.js-video-import', function () {
	        return _this.videoImport(false);
	      });
	      this.$form.on('click', '.js-add-file-list', function () {
	        return _this.addFileBtn(true);
	      });
	      this.$form.on('blur', '#title', function (event) {
	        return _this.titleChange(event);
	      });
	    }
	  }, {
	    key: 'itemDelete',
	    value: function itemDelete(event) {
	      var $parent = $(event.currentTarget).closest('li');
	      var mediaId = $parent.data('id');
	      var items = this.isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
	      if (items && items[mediaId]) {
	        delete items[mediaId];
	        $("#materials").val(JSON.stringify(items));
	      }
	      if ($parent.siblings('li').length <= 0) {
	        $("#materials").val(null);
	      }
	      $parent.remove();
	    }
	  }, {
	    key: 'videoImport',
	    value: function videoImport(state) {
	      this.addFile(state);
	    }
	  }, {
	    key: 'addFileBtn',
	    value: function addFileBtn(state) {
	      this.addFile(state);
	    }
	  }, {
	    key: 'initFileChooser',
	    value: function initFileChooser() {
	      var _this2 = this;
	
	      var fileSelect = function fileSelect(file) {
	        $("input[name=media]").val(JSON.stringify(file));
	        (0, _chooserUi.chooserUiOpen)();
	        _this2.addFile(false);
	        console.log(_this2.firstName);
	        if (_this2.firstName) {
	          $('#title').val(_this2.firstName);
	        } else {
	          $('#title').val('');
	        }
	        $('.js-current-file').text(file.name);
	      };
	
	      var fileChooser = new _fileChoose2["default"]();
	
	      fileChooser.on('select', fileSelect);
	    }
	  }, {
	    key: 'titleChange',
	    value: function titleChange(event) {
	
	      var $this = $(event.currentTarget);
	      this.firstName = $this.val();
	      console.log(this.firstName);
	    }
	  }, {
	    key: 'isEmpty',
	    value: function isEmpty(obj) {
	      return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
	    }
	  }, {
	    key: 'addFile',
	    value: function addFile(addToList) {
	      //@TODO重构代码
	      $('.js-success-redmine').hide();
	      if (this.isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
	        if (!addToList) {
	          $("#verifyLink").val($("#link").val());
	        }
	        var data = {
	          source: 'link',
	          id: $("#verifyLink").val(),
	          name: $("#verifyLink").val(),
	          link: $("#verifyLink").val(),
	          summary: $("#file-summary").val(),
	          size: 0
	        };
	        $('.js-current-file').text($("#verifyLink").val());
	        $("#media").val(JSON.stringify(data));
	      }
	
	      var media = this.isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());
	      var items = this.isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
	
	      if (!this.isEmpty(items) && items[media.id]) {
	        $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_exist_error_hint')).show();
	        setTimeout(function () {
	          $('.js-danger-redmine').slideUp();
	        }, 3000);
	        $('.js-current-file').text('');
	        $("#media").val(null);
	        media = null;
	        return;
	      }
	
	      if (!addToList) {
	        return;
	      }
	
	      if (addToList && this.isEmpty(media)) {
	        $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_error_hint')).show();
	        $('.js-current-file').text('');
	        setTimeout(function () {
	          $('.js-danger-redmine').slideUp();
	        }, 3000);
	        return;
	      }
	
	      $('.js-current-file').text('');
	      media.summary = $("#file-summary").val();
	      items[media.id] = media;
	      $("#materials").val(JSON.stringify(items));
	
	      $("#media").val(null);
	      $('#link').val(null);
	      $("#file-summary").val(null);
	
	      if (!this.firstName) {
	        this.firstName = media.name;
	        $('#title').val(media.name);
	      }
	
	      var item_tpl = '';
	      if (media.link) {
	        item_tpl = '\n    <li class="download-item " data-id="' + media.link + '">\n        <a class="gray-primary" href="' + media.link + '" target="_blank">' + media.name + '</a>\n        <a class="gray-primary phm btn-delete  js-btn-delete"  href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="' + Translator.trans('activity.download_manage.materials_delete_btn') + '"><i class="es-icon es-icon-delete"></i></a>\n        <span class="glyphicon glyphicon-new-window text-muted text-sm" title="' + Translator.trans('activity.download_manage.materials_delete_btn') + '"></span>\n    </li>\n  ';
	      } else {
	        item_tpl = '\n    <li class="download-item " data-id="' + media.id + '">\n      <a class="gray-primary" href="/materiallib/' + media.id + '/download">' + media.name + '</a>\n      <a class="gray-primary phm  btn-delete js-btn-delete" href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="' + Translator.trans('activity.download_manage.materials_delete_btn') + '"><i class="es-icon es-icon-delete"></i></a>\n    </li>\n  ';
	      }
	      $("#material-list").append(item_tpl);
	      $('[data-toggle="tooltip"]').tooltip();
	      $('.file-browser-item').removeClass('active');
	      $('.js-danger-redmine').hide();
	      $('.js-success-redmine').text(Translator.trans('activity.download_manage.materials_add_success_hint')).show();
	      setTimeout(function () {
	        $('.js-success-redmine').slideUp();
	      }, 3000);
	      if ($('.jq-validate-error:visible').length > 0) {
	        $("#step2-form").data('validator').form();
	      }
	    }
	  }]);
	
	  return DownLoad;
	}();
	
	exports["default"] = DownLoad;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _download = __webpack_require__("fbf0b6283b62b602eb6b");
	
	var _download2 = _interopRequireDefault(_download);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _download2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map