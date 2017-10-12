webpackJsonp(["app/js/question-manage/form/index"],{

/***/ "b13eefde5dd7af09b834":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _option = __webpack_require__("e7f6be29a6dce7725ed1");
	
	var _option2 = _interopRequireDefault(_option);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	var _unit = __webpack_require__("3c398f87808202f19beb");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	function InitOptionData(dataSource, inputValue, validatorDatas, seq, checked) {
	  var obj = {
	    optionId: Math.random().toString().replace('.', ''),
	    optionLabel: Translator.trans('activity.testpaper_manage.question_options') + (0, _unit.numberConvertLetter)(seq),
	    inputValue: inputValue,
	    checked: checked
	  };
	  validatorDatas.Options[obj.optionId] = inputValue.length > 0 ? 1 : 0;
	  if (checked) {
	    validatorDatas.checkedNum += 1;
	  }
	  dataSource.push(obj);
	}
	
	function _deleteOption(dataSource, validatorDatas, optionId) {
	  for (var i = 0; i < dataSource.length; i++) {
	    if (dataSource[i].optionId == optionId) {
	      if (dataSource[i].checked) {
	        validatorDatas.checkedNum = 0;
	      }
	      dataSource.splice(i, 1);
	      console.log(validatorDatas.Options[optionId]);
	      console.log(dataSource);
	      delete validatorDatas.Options[optionId];
	      i--;
	    } else {
	      dataSource[i].optionLabel = Translator.trans('activity.testpaper_manage.question_options') + (0, _unit.numberConvertLetter)(i + 1);
	    }
	  }
	}
	
	function _changeOptionChecked(dataSource, validatorDatas, id, checked, isRadio) {
	  var checkedNum = 0;
	  dataSource.map(function (item, index) {
	    if (!isRadio) {
	      if (item.optionId == id) {
	        dataSource[index].checked = !checked;
	      }
	    } else {
	      //单选
	      if (item.optionId == id && !checked) {
	        dataSource[index].checked = true;
	      } else if (!checked) {
	        dataSource[index].checked = false;
	      }
	    }
	    //计算选择的答案
	    console.log(dataSource[index].checked);
	    if (dataSource[index].checked) {
	      checkedNum++;
	    }
	  });
	  console.log(checkedNum);
	  validatorDatas.checkedNum = checkedNum;
	}
	
	function updateOption(dataSource, validatorDatas, id, value) {
	  dataSource.map(function (item, index) {
	    if (item.optionId == id) {
	      dataSource[index].inputValue = value;
	    }
	  });
	}
	
	var QuestionOptions = function (_Component) {
	  _inherits(QuestionOptions, _Component);
	
	  function QuestionOptions(props) {
	    _classCallCheck(this, QuestionOptions);
	
	    var _this = _possibleConstructorReturn(this, (QuestionOptions.__proto__ || Object.getPrototypeOf(QuestionOptions)).call(this, props));
	
	    _this.state = {
	      dataSource: [],
	      isValidator: false
	      //验证的数据
	    };_this.validatorDatas = {
	      checkedNum: 0,
	      Options: {}
	    };
	    var dataSource = _this.props.dataSource;
	    var dataAnswer = _this.props.dataAnswer;
	    if (dataSource.length > 0) {
	      dataSource.map(function (item, index) {
	        var checked = false;
	        for (var i = 0; i < dataAnswer.length; i++) {
	          if (index == dataAnswer[i]) {
	            checked = true;
	          }
	        }
	        InitOptionData(_this.state.dataSource, item, _this.validatorDatas, index + 1, checked);
	      });
	    } else {
	      for (var i = 1; i <= _this.props.defaultNum; i++) {
	        InitOptionData(_this.state.dataSource, '', _this.validatorDatas, i, false);
	      }
	    }
	    _this.subscriptionMessage();
	    console.log(_this.validatorOptions);
	    return _this;
	  }
	
	  _createClass(QuestionOptions, [{
	    key: 'subscriptionMessage',
	    value: function subscriptionMessage() {
	      var _this2 = this;
	
	      _postal2["default"].subscribe({
	        channel: "manage-question",
	        topic: "question-create-form-validator-start",
	        callback: function callback(data, envelope) {
	          _this2.validatorOptions(data);
	        }
	      });
	    }
	  }, {
	    key: 'publishMessage',
	    value: function publishMessage(isValidator) {
	      _postal2["default"].publish({
	        channel: "manage-question",
	        topic: "question-create-form-validator-end",
	        data: {
	          isValidator: isValidator
	        }
	      });
	    }
	  }, {
	    key: 'validatorOptions',
	    value: function validatorOptions(data) {
	      var validNum = 0;
	
	      //触发视觉
	      this.setState({
	        isValidator: data.isValidator
	      });
	
	      for (var option in this.validatorDatas.Options) {
	        validNum += this.validatorDatas.Options[option];
	      }
	
	      if (validNum < this.state.dataSource.length) {
	        console.log(' validNum is error ');
	        return;
	      }
	
	      if (this.validatorDatas.checkedNum < this.props.minCheckedNum) {
	        (0, _notify2["default"])('danger', Translator.trans('course.question.create.choose_min_answer_num_hint', { 'minCheckedNum': this.props.minCheckedNum }));
	      } else {
	        console.log('publishMessage');
	        this.publishMessage(true);
	      }
	    }
	  }, {
	    key: 'addOption',
	    value: function addOption() {
	      if (this.state.dataSource.length >= this.props.maxNum) {
	        (0, _notify2["default"])('danger', Translator.trans('course.question.create.choose_max_num_hint', { 'maxNum': this.props.maxNum }));
	        return;
	      }
	      InitOptionData(this.state.dataSource, '', this.validatorDatas, this.state.dataSource.length + 1, false);
	      this.setState({
	        dataSource: this.state.dataSource
	      });
	      console.log({ 'dataSource': this.state.dataSource });
	      console.log({ 'validatorDatas': this.validatorDatas });
	    }
	  }, {
	    key: 'changeOptionChecked',
	    value: function changeOptionChecked(id, checked) {
	      _changeOptionChecked(this.state.dataSource, this.validatorDatas, id, checked, this.props.isRadio);
	      this.setState({
	        dataSource: this.state.dataSource
	      });
	      if (this.validatorDatas.checkedNum <= 0) {
	        this.publishMessage(false);
	      }
	    }
	  }, {
	    key: 'deleteOption',
	    value: function deleteOption(id) {
	      if (this.state.dataSource.length <= this.props.minNum) {
	        (0, _notify2["default"])('danger', Translator.trans('course.question.create.choose_min_num_hint', { 'minNum': this.props.minNum }));
	        return;
	      }
	      _deleteOption(this.state.dataSource, this.validatorDatas, id);
	      this.setState({
	        dataSource: this.state.dataSource
	      });
	      console.log({ 'validatorDatas': this.validatorDatas });
	    }
	  }, {
	    key: 'updateInputValue',
	    value: function updateInputValue(id, value) {
	      updateOption(this.state.dataSource, this.validatorDatas, id, value);
	      this.validatorDatas.Options[id] = value.length > 0 ? 1 : 0;
	      if (value.length <= 0) {
	        this.publishMessage(false);
	      }
	      this.setState({
	        dataSource: this.state.dataSource
	      });
	
	      console.log(this.state.dataSource);
	    }
	  }, {
	    key: 'render',
	    value: function render() {
	      var _this3 = this;
	
	      var createNewName = Translator.trans('course.question.create_hint');
	      return _react2["default"].createElement(
	        'div',
	        { className: 'question-options-group' },
	        this.state.dataSource.map(function (item, index) {
	          return _react2["default"].createElement(_option2["default"], { imageUploadUrl: _this3.props.imageUploadUrl, imageDownloadUrl: _this3.props.imageDownloadUrl, isRadio: _this3.props.isRadio, publishMessage: function publishMessage(isValidator) {
	              return _this3.publishMessage(isValidator);
	            }, validatorDatas: _this3.validatorDatas, isValidator: _this3.state.isValidator, datas: item, key: index, index: index, deleteOption: function deleteOption(id) {
	              return _this3.deleteOption(id);
	            }, changeOptionChecked: function changeOptionChecked(id, checked) {
	              return _this3.changeOptionChecked(id, checked);
	            }, updateInputValue: function updateInputValue(id, value) {
	              return _this3.updateInputValue(id, value);
	            } });
	        }),
	        _react2["default"].createElement(
	          'div',
	          { className: 'form-group' },
	          _react2["default"].createElement(
	            'div',
	            { className: 'col-md-8 col-md-offset-2' },
	            _react2["default"].createElement(
	              'a',
	              { className: 'btn btn-success btn-sm pull-right', onClick: function onClick() {
	                  return _this3.addOption();
	                } },
	              createNewName
	            )
	          )
	        )
	      );
	    }
	  }]);
	
	  return QuestionOptions;
	}(_react.Component);
	
	exports["default"] = QuestionOptions;
	
	
	QuestionOptions.defaultProps = {
	  defaultNum: 4, //默认选项个数
	  maxNum: 10, //最多选项的个数
	  minNum: 2, //最少选项的个数
	  isRadio: false, //是否为单选
	  minCheckedNum: 1 //至少选择几个答案
	};

/***/ }),

/***/ "5a8ff9f4ed340a8713f6":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _questionChoice = __webpack_require__("2cf47b8094e4851a7f1f");
	
	var _questionChoice2 = _interopRequireDefault(_questionChoice);
	
	var _reactDom = __webpack_require__("5fdcf1aea784583ca083");
	
	var _reactDom2 = _interopRequireDefault(_reactDom);
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _questionOptions = __webpack_require__("b13eefde5dd7af09b834");
	
	var _questionOptions2 = _interopRequireDefault(_questionOptions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var SingleChoice = function (_QuestionChoice) {
	  _inherits(SingleChoice, _QuestionChoice);
	
	  function SingleChoice() {
	    _classCallCheck(this, SingleChoice);
	
	    return _possibleConstructorReturn(this, (SingleChoice.__proto__ || Object.getPrototypeOf(SingleChoice)).apply(this, arguments));
	  }
	
	  _createClass(SingleChoice, [{
	    key: 'initOptions',
	    value: function initOptions() {
	      _reactDom2["default"].render(_react2["default"].createElement(_questionOptions2["default"], { imageUploadUrl: this.imageUploadUrl, imageDownloadUrl: this.imageDownloadUrl, dataSource: this.dataSource, dataAnswer: this.dataAnswer }), document.getElementById('question-options'));
	    }
	  }]);
	
	  return SingleChoice;
	}(_questionChoice2["default"]);
	
	exports["default"] = SingleChoice;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	var _questionChoice = __webpack_require__("2cf47b8094e4851a7f1f");
	
	var _questionChoice2 = _interopRequireDefault(_questionChoice);
	
	var _questionSingleChoice = __webpack_require__("e85a87f5cf404e5d12c1");
	
	var _questionSingleChoice2 = _interopRequireDefault(_questionSingleChoice);
	
	var _questionUncertainChoice = __webpack_require__("5a8ff9f4ed340a8713f6");
	
	var _questionUncertainChoice2 = _interopRequireDefault(_questionUncertainChoice);
	
	var _questionDetermine = __webpack_require__("3c1fcf442037b440aea5");
	
	var _questionDetermine2 = _interopRequireDefault(_questionDetermine);
	
	var _questionFill = __webpack_require__("228720083c8f03b23e6d");
	
	var _questionFill2 = _interopRequireDefault(_questionFill);
	
	var _questionEssay = __webpack_require__("7fed9bfb1a62b2d3ee74");
	
	var _questionEssay2 = _interopRequireDefault(_questionEssay);
	
	var _questionMaterial = __webpack_require__("d10d1a490b8cc019f3a3");
	
	var _questionMaterial2 = _interopRequireDefault(_questionMaterial);
	
	var _selectLinkage = __webpack_require__("1be2a74362f00ba903a0");
	
	var _selectLinkage2 = _interopRequireDefault(_selectLinkage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionCreator = function () {
	  function QuestionCreator() {
	    _classCallCheck(this, QuestionCreator);
	  }
	
	  _createClass(QuestionCreator, null, [{
	    key: 'getCreator',
	    value: function getCreator(type, $form) {
	      switch (type) {
	        case 'single_choice':
	          QuestionCreator = new _questionSingleChoice2["default"]($form);
	          break;
	        case 'uncertain_choice':
	          QuestionCreator = new _questionUncertainChoice2["default"]($form);
	          break;
	        case 'choice':
	          QuestionCreator = new _questionChoice2["default"]($form);
	          break;
	        case 'determine':
	          QuestionCreator = new _questionDetermine2["default"]($form);
	          break;
	        case 'essay':
	          QuestionCreator = new _questionEssay2["default"]($form);
	          break;
	        case 'fill':
	          QuestionCreator = new _questionFill2["default"]($form);
	          break;
	        case 'material':
	          QuestionCreator = new _questionMaterial2["default"]($form);
	          break;
	        default:
	          QuestionCreator = new _formBase2["default"]($form);
	          QuestionCreator.initTitleEditor();
	          QuestionCreator.initAnalysisEditor();
	      }
	
	      return QuestionCreator;
	    }
	  }]);
	
	  return QuestionCreator;
	}();
	
	var $form = $('[data-role="question-form"]');
	var type = $('[data-role="question-form"]').find('[name="type"]').val();
	
	QuestionCreator.getCreator(type, $form);
	
	new _selectLinkage2["default"]($('[data-role="courseId"]'), $('[data-role="lessonId"]'));

/***/ }),

/***/ "fed3b995e613c074e80b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionFormBase = function () {
	  function QuestionFormBase($form) {
	    _classCallCheck(this, QuestionFormBase);
	
	    this.$form = $form;
	    this.titleFieldId = 'question-stem-field';
	    this.analysisFieldId = 'question-analysis-field';
	    this.validator = null;
	    this.titleEditorToolBarName = 'Minimal';
	    this._init();
	    this.attachmentActions = new _attachmentActions2["default"]($form);
	  }
	
	  _createClass(QuestionFormBase, [{
	    key: '_init',
	    value: function _init() {
	      this._initEvent();
	      this._initValidate();
	    }
	  }, {
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$form.on('click', '[data-role=submit]', function (event) {
	        return _this.submitForm(event);
	      });
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm(event) {
	      var submitType = $(event.currentTarget).data('submission');
	      this.$form.find('[name=submission]').val(submitType);
	      var self = this;
	
	      if (this.validator.form()) {
	        $(event.currentTarget).button('loading');
	        self.$form.submit();
	      }
	    }
	  }, {
	    key: '_initValidate',
	    value: function _initValidate() {
	      var validator = this.$form.validate({
	        onkeyup: false,
	        rules: {
	          '[data-role="target"]': {
	            required: true
	          },
	          difficulty: {
	            required: true
	          },
	          stem: {
	            required: true
	          },
	          score: {
	            required: true,
	            number: true,
	            max: 999,
	            min: 0
	          }
	        },
	        messages: {
	          '[data-role="target"]': Translator.trans('course.question.create.belong_required_error_hint'),
	          difficulty: Translator.trans('course.question.create.difficulty_required_error_hint')
	        }
	      });
	      this.validator = validator;
	    }
	  }, {
	    key: 'initTitleEditor',
	    value: function initTitleEditor(validator) {
	      var $target = $('#' + this.titleFieldId);
	      var editor = CKEDITOR.replace(this.titleFieldId, {
	        toolbar: this.titleEditorToolBarName,
	        filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
	        height: $target.height()
	      });
	
	      editor.on('change', function () {
	        $target.val(editor.getData());
	        validator.form();
	
	        console.log(editor.getData());
	      });
	      editor.on('blur', function () {
	        $target.val(editor.getData());
	        validator.form();
	        console.log(editor.getData());
	      });
	    }
	  }, {
	    key: 'initAnalysisEditor',
	    value: function initAnalysisEditor() {
	      var $target = $('#' + this.analysisFieldId);
	      var editor = CKEDITOR.replace(this.analysisFieldId, {
	        toolbar: this.titleEditorToolBarName,
	        filebrowserImageUploadUrl: $target.data('imageUploadUrl'),
	        height: $target.height()
	      });
	
	      editor.on('change', function () {
	        $target.val(editor.getData());
	      });
	    }
	  }, {
	    key: 'titleEditorToolBarName',
	    set: function set(toolbarName) {
	      this._titleEditorToolBarName = toolbarName;
	    },
	    get: function get() {
	      return this._titleEditorToolBarName;
	    }
	  }]);
	
	  return QuestionFormBase;
	}();
	
	exports["default"] = QuestionFormBase;

/***/ }),

/***/ "2cf47b8094e4851a7f1f":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	var _reactDom = __webpack_require__("5fdcf1aea784583ca083");
	
	var _reactDom2 = _interopRequireDefault(_reactDom);
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _questionOptions = __webpack_require__("b13eefde5dd7af09b834");
	
	var _questionOptions2 = _interopRequireDefault(_questionOptions);
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var Choice = function (_QuestionFormBase) {
	  _inherits(Choice, _QuestionFormBase);
	
	  function Choice($form) {
	    _classCallCheck(this, Choice);
	
	    var _this = _possibleConstructorReturn(this, (Choice.__proto__ || Object.getPrototypeOf(Choice)).call(this, $form));
	
	    _this.isSubmit = false;
	    _this.$submit = null;
	    _this.$questionOptions = $('#question-options');
	    _this.dataSource = _this.$questionOptions.data('choices');
	    _this.dataAnswer = _this.$questionOptions.data('answer');
	    if (_this.dataSource) {
	      _this.dataSource = JSON.parse(_this.dataSource);
	      _this.dataAnswer = JSON.parse(_this.dataAnswer);
	    } else {
	      _this.dataSource = [];
	      _this.dataAnswer = [];
	    }
	    _this.imageUploadUrl = _this.$questionOptions.data('imageUploadUrl');
	    _this.imageDownloadUrl = _this.$questionOptions.data('imageDownloadUrl');
	    _this.initTitleEditor(_this.validator);
	    _this.initAnalysisEditor();
	    _this.initOptions();
	    _this.subscriptionMessage();
	    return _this;
	  }
	
	  _createClass(Choice, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this2 = this;
	
	      this.$form.on('click', '[data-role=submit]', function (event) {
	        return _this2.submitForm(event);
	      });
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm(event) {
	      this.$submit = $(event.currentTarget);
	      var submitType = this.$submit.data('submission');
	      this.$form.find('[name=submission]').val(submitType);
	
	      if (this.validator.form() && this.isSubmit) {
	        this.submit();
	      }
	      if (!this.isSubmit) {
	        this.publishMessage();
	      }
	    }
	  }, {
	    key: 'submit',
	    value: function submit() {
	      this.$submit.button('loading');
	      this.$form.submit();
	    }
	  }, {
	    key: 'initOptions',
	    value: function initOptions() {
	      _reactDom2["default"].render(_react2["default"].createElement(_questionOptions2["default"], { imageUploadUrl: this.imageUploadUrl, imageDownloadUrl: this.imageDownloadUrl, dataSource: this.dataSource, dataAnswer: this.dataAnswer, minCheckedNum: 2 }), document.getElementById('question-options'));
	    }
	  }, {
	    key: 'publishMessage',
	    value: function publishMessage() {
	      console.log('publishMessage');
	      _postal2["default"].publish({
	        channel: "manage-question",
	        topic: "question-create-form-validator-start",
	        data: {
	          isValidator: true
	        }
	      });
	    }
	  }, {
	    key: 'subscriptionMessage',
	    value: function subscriptionMessage() {
	      var _this3 = this;
	
	      console.log('subscriptionMessage');
	      _postal2["default"].subscribe({
	        channel: "manage-question",
	        topic: "question-create-form-validator-end",
	        callback: function callback(data, envelope) {
	          _this3.isSubmit = data.isValidator;
	          console.log({
	            'subscriptionMessage': _this3.isSubmit
	          });
	          if (_this3.isSubmit && _this3.validator.form()) {
	            console.log('submit by subscriptionMessage');
	            _this3.submit();
	          }
	        }
	      });
	    }
	  }]);
	
	  return Choice;
	}(_formBase2["default"]);
	
	exports["default"] = Choice;

/***/ }),

/***/ "e7f6be29a6dce7725ed1":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var Options = function (_Component) {
	  _inherits(Options, _Component);
	
	  function Options(props) {
	    _classCallCheck(this, Options);
	
	    var _this = _possibleConstructorReturn(this, (Options.__proto__ || Object.getPrototypeOf(Options)).call(this, props));
	
	    _this.state = {
	      datas: _this.props.datas
	    };
	    _this.editor = null;
	    _this.editorBody = null;
	    _this.editorHtml = null;
	    return _this;
	  }
	
	  _createClass(Options, [{
	    key: 'componentDidMount',
	    value: function componentDidMount() {
	      console.log('componentDidMount');
	      this.initCkeditor();
	    }
	  }, {
	    key: 'deleteOption',
	    value: function deleteOption(event) {
	      this.editorHtml = null;
	      this.props.deleteOption(event.currentTarget.attributes["data-option-id"].value);
	    }
	  }, {
	    key: 'onChangeChecked',
	    value: function onChangeChecked(event) {
	      this.updateInputValue(this.editor.getData()); //fix ie 11,check befor blur;
	      this.props.changeOptionChecked(event.currentTarget.attributes["data-option-id"].value, this.props.datas.checked);
	    }
	  }, {
	    key: 'initCkeditor',
	    value: function initCkeditor(dataSourceUi) {
	      if (!this.editor) {
	        this.editor = CKEDITOR.replace(this.props.datas.optionId, {
	          toolbar: 'Minimal',
	          filebrowserImageUploadUrl: this.props.imageUploadUrl,
	          height: 120
	        });
	        var self = this;
	        this.editor.on("instanceReady", function () {
	          self.editorBody = $('#' + [self.props.datas.optionId]).parent().find('iframe').contents().find('body');
	          //setData两个问题：1、引发事件失效 2、死循环触发；
	        });
	        this.editor.on('change', function () {
	          console.log('change' + self.editor.getData());
	          setTimeout(function () {
	            self.updateInputValue(self.editor.getData());
	          }, 100);
	        });
	        this.editor.on('blur', function () {
	          //fix ie 11 中文输入
	          console.log('blur' + self.editor.getData());
	          setTimeout(function () {
	            self.updateInputValue(self.editor.getData());
	          }, 100);
	        });
	      } else {
	        this.editor.setData(datas.inputValue);
	      }
	    }
	  }, {
	    key: 'updateInputValue',
	    value: function updateInputValue(inputValue) {
	      console.log(inputValue);
	
	      this.editorHtml = inputValue;
	      this.props.updateInputValue(this.props.datas.optionId, inputValue);
	    }
	  }, {
	    key: 'render',
	    value: function render() {
	      var _this2 = this;
	
	      var showDanger = this.props.isValidator && this.props.datas.inputValue.length <= 0;
	      var type = 'checkbox';
	      if (this.props.isRadio) {
	        type = 'radio';
	      }
	      if (this.editorBody && this.editorHtml != this.props.datas.inputValue) {
	        this.editorBody.html(this.props.datas.inputValue);
	      }
	
	      var correctName = Translator.trans('course.question.right_answer_hint');
	      return _react2["default"].createElement(
	        'div',
	        { className: 'form-group' },
	        _react2["default"].createElement(
	          'div',
	          { className: 'col-sm-2 control-label' },
	          _react2["default"].createElement(
	            'label',
	            { className: 'choice-label control-label-required' },
	            this.props.datas.optionLabel
	          )
	        ),
	        _react2["default"].createElement(
	          'div',
	          { className: 'col-sm-8 controls' },
	          _react2["default"].createElement('textarea', { className: 'form-control datas-input col-md-8', id: this.props.datas.optionId, defaultValue: this.props.datas.inputValue, name: 'choices[]', value: this.props.datas.inputValue, 'data-image-upload-url': this.props.imageUploadUrl, 'data-image-download-url': this.props.imageDownloadUrl }),
	          _react2["default"].createElement(
	            'div',
	            { className: 'mtm' },
	            _react2["default"].createElement(
	              'label',
	              null,
	              _react2["default"].createElement('input', { type: type, name: 'answer[]', 'data-option-id': this.props.datas.optionId, value: this.props.index, checked: this.props.datas.checked, className: 'answer-checkbox', onChange: function onChange(event) {
	                  return _this2.onChangeChecked(event);
	                } }),
	              ' ',
	              correctName
	            )
	          ),
	          _react2["default"].createElement(
	            'p',
	            { className: showDanger ? 'color-danger' : 'hidden' },
	            Translator.trans('course.question.right_answer_content_hint')
	          )
	        ),
	        _react2["default"].createElement(
	          'div',
	          { className: 'col-sm-2' },
	          _react2["default"].createElement(
	            'a',
	            { className: 'btn btn-default btn-sm', 'data-option-id': this.props.datas.optionId, onClick: function onClick(event) {
	                return _this2.deleteOption(event);
	              }, href: 'javascript:;' },
	            _react2["default"].createElement('i', { className: 'glyphicon glyphicon-trash' })
	          )
	        )
	      );
	    }
	  }]);
	
	  return Options;
	}(_react.Component);
	
	exports["default"] = Options;

/***/ }),

/***/ "7fed9bfb1a62b2d3ee74":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var Essay = function (_QuestionFormBase) {
	  _inherits(Essay, _QuestionFormBase);
	
	  function Essay($form) {
	    _classCallCheck(this, Essay);
	
	    var _this = _possibleConstructorReturn(this, (Essay.__proto__ || Object.getPrototypeOf(Essay)).call(this, $form));
	
	    _this.initTitleEditor(_this.validator);
	    _this.initAnalysisEditor();
	
	    _this.answerFieldId = 'question-answer-field';
	    _this.$answerField = $('#' + _this.answerFieldId);
	
	    _this.init();
	    return _this;
	  }
	
	  _createClass(Essay, [{
	    key: 'init',
	    value: function init() {
	      var _this2 = this;
	
	      this.$answerField.rules('add', {
	        required: true
	      });
	
	      var editor = CKEDITOR.replace(this.answerFieldId, {
	        toolbar: 'Minimal',
	        filebrowserImageUploadUrl: this.$answerField.data('imageUploadUrl'),
	        height: this.$answerField.height()
	      });
	
	      editor.on('change', function () {
	        _this2.$answerField.val(editor.getData());
	      });
	      editor.on('blur', function () {
	        _this2.$answerField.val(editor.getData());
	      });
	    }
	  }]);
	
	  return Essay;
	}(_formBase2["default"]);
	
	exports["default"] = Essay;

/***/ }),

/***/ "228720083c8f03b23e6d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	jQuery.validator.addMethod("fillCheck", function (value, element) {
	  return this.optional(element) || /(\[\[(.+?)\]\])/i.test(value);
	}, Translator.trans('course.question.create.fill_hint'));
	
	var Fill = function (_QuestionFormBase) {
	  _inherits(Fill, _QuestionFormBase);
	
	  function Fill($form) {
	    _classCallCheck(this, Fill);
	
	    var _this = _possibleConstructorReturn(this, (Fill.__proto__ || Object.getPrototypeOf(Fill)).call(this, $form));
	
	    _this.titleEditorToolBarName = 'Question';
	    _this.initTitleEditor(_this.validator);
	    _this.initAnalysisEditor();
	
	    _this.$titleField = $('#' + _this.titleFieldId);
	    _this.init();
	    return _this;
	  }
	
	  _createClass(Fill, [{
	    key: 'init',
	    value: function init() {
	      this.$titleField.rules('add', {
	        fillCheck: true
	      });
	    }
	  }]);
	
	  return Fill;
	}(_formBase2["default"]);
	
	exports["default"] = Fill;

/***/ }),

/***/ "d10d1a490b8cc019f3a3":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var Material = function (_QuestionFormBase) {
	  _inherits(Material, _QuestionFormBase);
	
	  function Material($form) {
	    _classCallCheck(this, Material);
	
	    var _this = _possibleConstructorReturn(this, (Material.__proto__ || Object.getPrototypeOf(Material)).call(this, $form));
	
	    _this.initTitleEditor(_this.validator);
	    _this.initAnalysisEditor();
	    return _this;
	  }
	
	  return Material;
	}(_formBase2["default"]);
	
	exports["default"] = Material;

/***/ }),

/***/ "e85a87f5cf404e5d12c1":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _questionChoice = __webpack_require__("2cf47b8094e4851a7f1f");
	
	var _questionChoice2 = _interopRequireDefault(_questionChoice);
	
	var _reactDom = __webpack_require__("5fdcf1aea784583ca083");
	
	var _reactDom2 = _interopRequireDefault(_reactDom);
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _questionOptions = __webpack_require__("b13eefde5dd7af09b834");
	
	var _questionOptions2 = _interopRequireDefault(_questionOptions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var SingleChoice = function (_QuestionChoice) {
	  _inherits(SingleChoice, _QuestionChoice);
	
	  function SingleChoice() {
	    _classCallCheck(this, SingleChoice);
	
	    return _possibleConstructorReturn(this, (SingleChoice.__proto__ || Object.getPrototypeOf(SingleChoice)).apply(this, arguments));
	  }
	
	  _createClass(SingleChoice, [{
	    key: 'initOptions',
	    value: function initOptions() {
	      _reactDom2["default"].render(_react2["default"].createElement(_questionOptions2["default"], { imageUploadUrl: this.imageUploadUrl, imageDownloadUrl: this.imageDownloadUrl, dataSource: this.dataSource, dataAnswer: this.dataAnswer, isRadio: true }), document.getElementById('question-options'));
	    }
	  }]);
	
	  return SingleChoice;
	}(_questionChoice2["default"]);
	
	exports["default"] = SingleChoice;

/***/ }),

/***/ "3c1fcf442037b440aea5":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _formBase = __webpack_require__("fed3b995e613c074e80b");
	
	var _formBase2 = _interopRequireDefault(_formBase);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var Datermine = function (_QuestionFormBase) {
		_inherits(Datermine, _QuestionFormBase);
	
		function Datermine($form) {
			_classCallCheck(this, Datermine);
	
			var _this = _possibleConstructorReturn(this, (Datermine.__proto__ || Object.getPrototypeOf(Datermine)).call(this, $form));
	
			_this.initTitleEditor(_this.validator);
			_this.initAnalysisEditor();
	
			_this.$answerField = $('[name="answer\[\]"]');
	
			_this.init();
			return _this;
		}
	
		_createClass(Datermine, [{
			key: 'init',
			value: function init() {
				this.$answerField.rules('add', {
					required: true,
					messages: {
						required: Translator.trans('course.question.create.right_answer_required_error_hint')
					}
				});
			}
		}]);
	
		return Datermine;
	}(_formBase2["default"]);
	
	exports["default"] = Datermine;

/***/ })

});
//# sourceMappingURL=index.js.map