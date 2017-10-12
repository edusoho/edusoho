webpackJsonp(["app/js/course-manage/students/add/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var StudentAdd = function () {
	  function StudentAdd() {
	    _classCallCheck(this, StudentAdd);
	
	    this.init();
	  }
	
	  _createClass(StudentAdd, [{
	    key: 'init',
	    value: function init() {
	      var $form = $('#student-add-form');
	      var validator = $form.validate({
	        onkeyup: false,
	        currentDom: '#student-add-submit',
	        rules: {
	          queryfield: {
	            required: true,
	            remote: {
	              url: $('#student-nickname').data('url'),
	              type: 'get',
	              data: {
	                'value': function value() {
	                  return $('#student-nickname').val();
	                }
	              }
	            }
	          }
	        },
	        messages: {
	          queryfield: {
	            remote: Translator.trans('course_manage.student_create.field_required_error_hint')
	          }
	        }
	      });
	
	      $('#student-add-submit').click(function (event) {
	        if (validator.form()) {
	          $form.submit();
	        }
	      });
	    }
	  }]);
	
	  return StudentAdd;
	}();
	
	new StudentAdd();

/***/ })
]);
//# sourceMappingURL=index.js.map