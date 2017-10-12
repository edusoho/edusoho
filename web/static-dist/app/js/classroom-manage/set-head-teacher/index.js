webpackJsonp(["app/js/classroom-manage/set-head-teacher/index"],{

/***/ "55e73d7afebf9c74b73e":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _multiInput = __webpack_require__("26fa658edb0135ccf5db");
	
	var _multiInput2 = _interopRequireDefault(_multiInput);
	
	var _list = __webpack_require__("d0399763e3c229c64154");
	
	var _list2 = _interopRequireDefault(_list);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	function initItem(dataSourceUi, data, index, props) {
	  var _outputValue;
	
	  var obj = {
	    itemId: Math.random(),
	    nickname: data[props.nickname],
	    isVisible: data[props.isVisible] == 1 ? true : false,
	    avatar: data[props.avatar],
	    seq: index,
	    id: data[props.id],
	    outputValue: (_outputValue = {}, _defineProperty(_outputValue, props.id, data[props.id]), _defineProperty(_outputValue, props.isVisible, data[props.isVisible]), _outputValue)
	  };
	  dataSourceUi.push(obj);
	}
	
	function updateChecked(dataSourceUi, id) {
	  dataSourceUi.map(function (item, index) {
	    if (item.itemId == id) {
	      dataSourceUi[index].isVisible = !dataSourceUi[index].isVisible;
	      dataSourceUi[index].outputValue.isVisible = dataSourceUi[index].isVisible ? 1 : 0;
	    }
	  });
	}
	
	var PersonaMultiInput = function (_MultiInput) {
	  _inherits(PersonaMultiInput, _MultiInput);
	
	  function PersonaMultiInput(props) {
	    _classCallCheck(this, PersonaMultiInput);
	
	    var _this = _possibleConstructorReturn(this, (PersonaMultiInput.__proto__ || Object.getPrototypeOf(PersonaMultiInput)).call(this, props));
	
	    _this.onChecked = function (event) {
	      var id = event.currentTarget.value;
	      updateChecked(_this.state.dataSourceUi, id);
	      _this.setState({
	        dataSourceUi: _this.state.dataSourceUi
	      });
	    };
	
	    _this.addItem = function (value, data) {
	      if (!data) {
	        return;
	      }
	      //@TODO重复添加提示
	      if (_this.props.replaceItem) {
	        _this.state.dataSourceUi = [];
	      }
	      initItem(_this.state.dataSourceUi, data, _this.state.dataSourceUi.length + 1, _this.props);
	
	      _this.setState({
	        dataSourceUi: _this.state.dataSourceUi
	      });
	    };
	
	    return _this;
	  }
	
	  _createClass(PersonaMultiInput, [{
	    key: 'componentWillMount',
	    value: function componentWillMount() {
	      var _this2 = this;
	
	      this.state = {
	        dataSourceUi: []
	      };
	      this.props.dataSource.map(function (item, index) {
	        initItem(_this2.state.dataSourceUi, item, index + 1, _this2.props);
	      });
	    }
	  }, {
	    key: 'getChildContext',
	    value: function getChildContext() {
	      return {
	        addable: this.props.addable,
	        searchable: this.props.searchable,
	        sortable: this.props.sortable,
	        listClassName: this.props.listClassName,
	        inputName: this.props.inputName,
	        showCheckbox: this.props.showCheckbox,
	        showDeleteBtn: this.props.showDeleteBtn,
	        checkBoxName: this.props.checkBoxName,
	        onChecked: this.onChecked,
	        removeItem: this.removeItem,
	        sortItem: this.sortItem,
	        addItem: this.addItem,
	        dataSourceUi: this.state.dataSourceUi
	      };
	    }
	  }, {
	    key: 'getList',
	    value: function getList() {
	      return _react2["default"].createElement(_list2["default"], null);
	    }
	  }]);
	
	  return PersonaMultiInput;
	}(_multiInput2["default"]);
	
	exports["default"] = PersonaMultiInput;
	
	
	PersonaMultiInput.propTypes = _extends({}, _multiInput2["default"].propTypes, {
	  id: _react2["default"].PropTypes.string,
	  nickname: _react2["default"].PropTypes.string,
	  avatar: _react2["default"].PropTypes.string,
	  isVisible: _react2["default"].PropTypes.string,
	  replaceItem: _react2["default"].PropTypes.bool,
	  showCheckbox: _react2["default"].PropTypes.bool,
	  showDeleteBtn: _react2["default"].PropTypes.bool
	});
	
	PersonaMultiInput.defaultProps = _extends({}, _multiInput2["default"].defaultProps, {
	  id: 'id',
	  nickname: 'nickname',
	  avatar: 'avatar',
	  isVisible: 'isVisible',
	  replaceItem: false,
	  showCheckbox: true,
	  showDeleteBtn: true
	});
	
	PersonaMultiInput.childContextTypes = _extends({}, _multiInput2["default"].childContextTypes, {
	  showCheckbox: _react2["default"].PropTypes.bool,
	  showDeleteBtn: _react2["default"].PropTypes.bool,
	  checkBoxName: _react2["default"].PropTypes.string,
	  onChecked: _react2["default"].PropTypes.func
	});

/***/ }),

/***/ "d0399763e3c229c64154":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _sortable = __webpack_require__("8f840897d9471c8c1fbd");
	
	var _sortable2 = _interopRequireDefault(_sortable);
	
	var _part = __webpack_require__("3fb32ce3bf28bfad7e02");
	
	var _list = __webpack_require__("fdfc24440b4845bd47af");
	
	var _list2 = _interopRequireDefault(_list);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var List = function (_MultiInputList) {
	  _inherits(List, _MultiInputList);
	
	  function List(props) {
	    _classCallCheck(this, List);
	
	    return _possibleConstructorReturn(this, (List.__proto__ || Object.getPrototypeOf(List)).call(this, props));
	  }
	
	  _createClass(List, [{
	    key: 'render',
	    value: function render() {
	      var _this2 = this;
	
	      var _context = this.context,
	          dataSourceUi = _context.dataSourceUi,
	          listClassName = _context.listClassName,
	          sortable = _context.sortable,
	          showCheckbox = _context.showCheckbox,
	          showDeleteBtn = _context.showDeleteBtn,
	          checkBoxName = _context.checkBoxName,
	          inputName = _context.inputName;
	
	      var name = '';
	      if (dataSourceUi.length > 0) {
	        name = 'list-group';
	      }
	      return _react2["default"].createElement(
	        'ul',
	        { id: this.listId, className: 'multi-list sortable-list ' + name + ' ' + listClassName },
	        dataSourceUi.map(function (item, i) {
	          return _react2["default"].createElement(
	            'li',
	            { className: 'list-group-item', id: item.itemId, key: i, 'data-seq': item.seq },
	            sortable && _react2["default"].createElement('i', { className: 'es-icon es-icon-yidong mrl color-gray inline-block vertical-middle' }),
	            _react2["default"].createElement('img', { className: 'avatar-sm avatar-sm-square mrm', src: item.avatar }),
	            _react2["default"].createElement(
	              'span',
	              { className: 'label-name text-overflow inline-block vertical-middle' },
	              item.nickname
	            ),
	            _react2["default"].createElement(
	              'label',
	              { className: showCheckbox ? '' : 'hidden' },
	              _react2["default"].createElement('input', { type: 'checkbox', name: checkBoxName + item.id, checked: item.isVisible, onChange: function onChange(event) {
	                  return _this2.context.onChecked(event);
	                }, value: item.itemId }),
	              Translator.trans('course.manage.teacher_display_label')
	            ),
	            _react2["default"].createElement(
	              'a',
	              { className: showDeleteBtn ? 'pull-right link-gray mtm' : 'hidden', onClick: function onClick(event) {
	                  return _this2.context.removeItem(event);
	                }, 'data-item-id': item.itemId },
	              _react2["default"].createElement('i', { className: 'es-icon es-icon-close01 text-12' })
	            ),
	            _react2["default"].createElement('input', { type: 'hidden', name: inputName, value: item.id })
	          );
	        })
	      );
	    }
	  }]);
	
	  return List;
	}(_list2["default"]);
	
	exports["default"] = List;
	;
	
	List.contextTypes = _extends({}, _list2["default"].contextTypes, {
	  showCheckbox: _react2["default"].PropTypes.bool,
	  showDeleteBtn: _react2["default"].PropTypes.bool,
	  checkBoxName: _react2["default"].PropTypes.string,
	  onChecked: _react2["default"].PropTypes.func
	});

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _reactDom = __webpack_require__("5fdcf1aea784583ca083");
	
	var _reactDom2 = _interopRequireDefault(_reactDom);
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _personaMultiInput = __webpack_require__("55e73d7afebf9c74b73e");
	
	var _personaMultiInput2 = _interopRequireDefault(_personaMultiInput);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	_reactDom2["default"].render(_react2["default"].createElement(_personaMultiInput2["default"], {
	  replaceItem: true,
	  sortable: false,
	  addable: true,
	  showCheckbox: false,
	  inputName: 'ids[]',
	  dataSource: $('#classroom-head-teacher').data('teacher'), outputDataElement: 'teachers', searchable: { enable: true, url: $('#classroom-head-teacher').data('url') + "?q=" }, showDeleteBtn: false }), document.getElementById('classroom-head-teacher'));

/***/ })

});
//# sourceMappingURL=index.js.map