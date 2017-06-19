webpackJsonp(["app/js/testpaper-manage/create/index"],{

/***/ "1be2a74362f00ba903a0":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var SelectLinkage = function () {
	  function SelectLinkage($select1, $select2) {
	    _classCallCheck(this, SelectLinkage);
	
	    this.select1 = $select1;
	    this.select2 = $select2;
	
	    this._initEvent();
	  }
	
	  _createClass(SelectLinkage, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.select1.on('change', function (event) {
	        return _this._selectChange(event);
	      });
	    }
	  }, {
	    key: '_selectChange',
	    value: function _selectChange(event) {
	      var url = this.select1.data('url');
	      var value = this.select1.val();
	      var self = this;
	
	      self.select2.text('');
	
	      if (value == 0) {
	        this.select2.hide();
	        return;
	      }
	
	      $.post(url, { courseId: value }, function (result) {
	        if (result != '') {
	          var option = '<option value="0">请选择</option>';
	          $.each(result, function (index, task) {
	            option += '<option value="' + task.id + '">' + task.title + '</option>';
	          });
	          self.select2.append(option);
	          self.select2.show();
	        } else {
	          self.select2.hide();
	        }
	      });
	    }
	  }]);
	
	  return SelectLinkage;
	}();
	
	exports.default = SelectLinkage;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _sortable = __webpack_require__("8f840897d9471c8c1fbd");
	
	var _sortable2 = _interopRequireDefault(_sortable);
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var _selectLinkage = __webpack_require__("1be2a74362f00ba903a0");
	
	var _selectLinkage2 = _interopRequireDefault(_selectLinkage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var TestpaperForm = function () {
	  function TestpaperForm($form) {
	    _classCallCheck(this, TestpaperForm);
	
	    this.$form = $form;
	    this.$description = this.$form.find('[name="description"]');
	    this.validator = null;
	    this.difficultySlider = null;
	    this._initEvent();
	    this._initValidate();
	    this._initSortList();
	    this.scoreSlider = null;
	  }
	
	  _createClass(TestpaperForm, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$form.on('click', '[data-role="submit"]', function (event) {
	        return _this._submit(event);
	      });
	      this.$form.on('click', '[name="mode"]', function (event) {
	        return _this.changeMode(event);
	      });
	      this.$form.on('click', '[name="range"]', function (event) {
	        return _this.changeRange(event);
	      });
	      this.$form.on('blur', '[data-role="count"]', function (event) {
	        return _this.changeCount(event);
	      });
	    }
	  }, {
	    key: 'initScoreSlider',
	    value: function initScoreSlider(passScore, score) {
	      var scoreSlider = document.getElementById('score-slider');
	      var option = {
	        start: passScore,
	        connect: [true, false],
	        tooltips: [true],
	        step: 1,
	        range: {
	          'min': 0,
	          'max': score
	        }
	      };
	      if (this.scoreSlider) {
	        this.scoreSlider.updateOptions(option);
	      } else {
	        this.scoreSlider = noUiSlider.create(scoreSlider, option);
	        scoreSlider.noUiSlider.on('update', function (values, handle) {
	          $('.noUi-tooltip').text((values[handle] / score * 100).toFixed(0) + '%');
	          $('.js-passScore').text(parseInt(values[handle]));
	        });
	      }
	      $('.noUi-handle').attr('data-placement', 'top').attr('data-original-title', '\u8FBE\u6807\u5206\u6570\uFF1A<span class="js-passScore">' + passScore + '</span>\u5206').attr('data-container', 'body');
	      $('.noUi-handle').tooltip({ html: true });
	      $('.noUi-tooltip').text((passScore / score * 100).toFixed(0) + '%');
	    }
	  }, {
	    key: 'changeMode',
	    value: function changeMode(event) {
	      var $this = $(event.currentTarget);
	      if ($this.val() == 'difficulty') {
	        this.$form.find('#difficulty-form-group').removeClass('hidden');
	        this.initDifficultySlider();
	      } else {
	        this.$form.find('#difficulty-form-group').addClass('hidden');
	      }
	    }
	  }, {
	    key: 'changeRange',
	    value: function changeRange(event) {
	      var $this = $(event.currentTarget);
	      $this.val() == 'course' ? this.$form.find('#testpaper-range-selects').addClass('hidden') : this.$form.find('#testpaper-range-selects').removeClass('hidden');
	    }
	  }, {
	    key: 'initDifficultySlider',
	    value: function initDifficultySlider() {
	      if (!this.difficultySlider) {
	        var sliders = document.getElementById('difficulty-percentage-slider');
	        this.difficultySlider = noUiSlider.create(sliders, {
	          start: [30, 70],
	          margin: 30,
	          range: {
	            'min': 0,
	            'max': 100
	          },
	          step: 1,
	          connect: [true, true, true],
	          serialization: {
	            resolution: 1
	          }
	        });
	        sliders.noUiSlider.on('update', function (values, handle) {
	          var simplePercentage = parseInt(values[0]),
	              normalPercentage = values[1] - values[0],
	              difficultyPercentage = 100 - values[1];
	          $('.js-simple-percentage-text').html(Translator.trans('简单') + simplePercentage + '%');
	          $('.js-normal-percentage-text').html(Translator.trans('一般') + normalPercentage + '%');
	          $('.js-difficulty-percentage-text').html(Translator.trans('困难') + difficultyPercentage + '%');
	          $('input[name="percentages[simple]"]').val(simplePercentage);
	          $('input[name="percentages[normal]"]').val(normalPercentage);
	          $('input[name="percentages[difficulty]"]').val(difficultyPercentage);
	        });
	      }
	    }
	  }, {
	    key: '_initEditor',
	    value: function _initEditor(validator) {
	      var _this2 = this;
	
	      var editor = CKEDITOR.replace(this.$description.attr('id'), {
	        toolbar: 'Simple',
	        filebrowserImageUploadUrl: this.$description.data('imageUploadUrl'),
	        height: 100
	      });
	      editor.on('change', function (a, b, c) {
	        _this2.$description.val((0, _utils.delHtmlTag)(editor.getData()));
	      });
	      editor.on('blur', function () {
	        _this2.$description.val((0, _utils.delHtmlTag)(editor.getData())); //fix ie11
	        validator.form();
	      });
	    }
	  }, {
	    key: 'changeCount',
	    value: function changeCount() {
	      var num = 0;
	      this.$form.find('[data-role="count"]').each(function (index, item) {
	        num += parseInt($(item).val());
	      });
	      this.$form.find('[name="questioncount"]').val(num > 0 ? num : null);
	    }
	  }, {
	    key: '_initValidate',
	    value: function _initValidate() {
	      this.validator = this.$form.validate({
	        rules: {
	          name: {
	            required: true,
	            maxlength: 50,
	            trim: true
	          },
	          description: {
	            required: true,
	            maxlength: 500,
	            trim: true
	          },
	          limitedTime: {
	            min: 0,
	            max: 10000,
	            digits: true
	          },
	          mode: {
	            required: true
	          },
	          range: {
	            required: true
	          },
	          questioncount: {
	            required: true
	          }
	        },
	        messages: {
	          questioncount: "请选择题目",
	          name: {
	            required: "请输入试卷名称",
	            maxlength: "最多只能输入50个字符"
	          },
	          description: {
	            required: "请输入试卷描述",
	            maxlength: "最多只能输入500个字符"
	          },
	          mode: "请选择生成方式",
	          range: "请选择出题范围"
	        }
	      });
	      this.$form.find('.testpaper-question-option-item').each(function () {
	        var self = $(this);
	        self.find('[data-role="count"]').rules('add', {
	          min: 0,
	          max: function max() {
	            return parseInt(self.find('[role="questionNum"]').text());
	          },
	          digits: true
	        });
	
	        self.find('[data-role="score"]').rules('add', {
	          min: 0,
	          max: 100,
	          digits: true
	        });
	
	        if (self.find('[data-role="missScore"]').length > 0) {
	          self.find('[data-role="missScore"]').rules('add', {
	            min: 0,
	            max: function max() {
	              return parseInt(self.find('[data-role="score"]').val());
	            },
	            digits: true
	          });
	        }
	      });
	      this._initEditor(this.validator);
	    }
	  }, {
	    key: '_initSortList',
	    value: function _initSortList() {
	      (0, _sortable2.default)({
	        element: '#testpaper-question-options',
	        itemSelector: '.testpaper-question-option-item',
	        handle: '.question-type-sort-handler',
	        ajax: false
	      });
	    }
	  }, {
	    key: '_submit',
	    value: function _submit(event) {
	      var _this3 = this;
	
	      var $target = $(event.currentTarget);
	      var status = this.validator.form();
	
	      if (status) {
	        $.post($target.data('checkUrl'), this.$form.serialize(), function (result) {
	          if (result.status == 'no') {
	            $('.js-build-check').html('该范围内题目数量不足');
	          } else {
	            $('.js-build-check').html('');
	
	            $target.button('loading').addClass('disabled');
	            _this3.$form.submit();
	          }
	        });
	      }
	    }
	  }]);
	
	  return TestpaperForm;
	}();
	
	new TestpaperForm($('#testpaper-form'));
	new _selectLinkage2.default($('[name="ranges[courseId]"]'), $('[name="ranges[lessonId]"]'));
	
	$('[name="ranges[courseId]"]').change(function () {
	  var url = $(this).data('checkNumUrl');
	  checkQuestionNum(url);
	});
	
	$('[name="ranges[lessonId]"]').change(function () {
	  var url = $(this).data('checkNumUrl');
	  checkQuestionNum(url);
	});
	
	function checkQuestionNum(url) {
	  var courseId = $('[name="ranges[courseId]"]').val();
	  var lessonId = $('[name="ranges[lessonId]"]').val();
	
	  $.post(url, { courseId: courseId, lessonId: lessonId }, function (data) {
	    $('[role="questionNum"]').text(0);
	
	    $.each(data, function (i, n) {
	      $("[type='" + i + "']").text(n.questionNum);
	    });
	  });
	}

/***/ }),

/***/ "8f840897d9471c8c1fbd":
/***/ (function(module, exports) {

	import 'jquery-sortable';
	
	var sortList = function sortList(options) {
	  var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function (data) {};
	
	  var defaultOptions = {
	    element: '#sortable-list',
	    distance: 20,
	    itemSelector: "li.drag",
	    ajax: true
	  };
	
	  var settings = Object.assign({}, defaultOptions, options);
	  var $list = $(settings.element).sortable(Object.assign({}, settings, {
	    onDrop: function onDrop(item, container, _super) {
	      _super(item, container);
	      var data = $list.sortable("serialize").get();
	      callback(data);
	      if (settings.ajax) {
	        $.post($list.data('sortUrl'), { ids: data }, function (response) {
	          settings.success ? settings.success(response) : document.location.reload();
	        });
	      }
	    },
	
	    serialize: function serialize(parent, children, isContainer) {
	      return isContainer ? children : parent.attr('id');
	    }
	
	  }));
	};
	
	export default sortList;

/***/ })

});