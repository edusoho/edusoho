webpackJsonp(["app/js/courseset-manage/detail/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import ReactDOM from 'react-dom';
	import React from 'react';
	import MultiInput from 'app/common/component/multi-input';
	import postal from 'postal';
	
	var detail = function () {
	  function detail() {
	    _classCallCheck(this, detail);
	
	    this.init();
	  }
	
	  _createClass(detail, [{
	    key: 'init',
	    value: function init() {
	      this.initCkeditor();
	      this.renderMultiGroupComponent('course-goals', 'goals');
	      this.renderMultiGroupComponent('intended-students', 'audiences');
	      this.submitForm();
	    }
	  }, {
	    key: 'initCkeditor',
	    value: function initCkeditor() {
	      CKEDITOR.replace('summary', {
	        allowedContent: true,
	        toolbar: 'Detail',
	        filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
	      });
	    }
	  }, {
	    key: 'renderMultiGroupComponent',
	    value: function renderMultiGroupComponent(elementId, name) {
	      var datas = $('#' + elementId).data('init-value');
	      ReactDOM.render(React.createElement(MultiInput, {
	        blurIsAdd: true,
	        sortable: true,
	        dataSource: datas,
	        inputName: name + "[]",
	        outputDataElement: name }), document.getElementById(elementId));
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm() {
	      var _this = this;
	
	      $('#courseset-submit').click(function (event) {
	        _this.publishAddMessage();
	        $(event.currentTarget).button('loading');
	        $('#courseset-detail-form').submit();
	      });
	    }
	  }, {
	    key: 'publishAddMessage',
	    value: function publishAddMessage() {
	      postal.publish({
	        channel: "courseInfoMultiInput",
	        topic: "addMultiInput"
	      });
	    }
	  }]);
	
	  return detail;
	}();
	
	new detail();

/***/ })
]);