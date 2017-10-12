webpackJsonp(["app/js/classroom/create/index"],{

/***/ "5d31da2441e6b75d3a07":
/***/ (function(module, exports) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	    value: true
	});
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	var classroomCreate = function classroomCreate() {
	    var _$$select;
	
	    if ($("#create-classroom").val() != '') {
	        if ($("#showable-open").data('showable') == 1) {
	            $("#showable-open").attr('checked', 'checked');
	            if ($("#buyable-open").data('buyable') == 1) {
	                $("#buyable-open").attr('checked', 'checked');
	            } else {
	                $("#buyable-close").attr('checked', 'checked');
	            }
	        } else {
	            $("#showable-close").attr('checked', 'checked');
	            if ($("#buyable-open").data('buyable') == 1) {
	                $("#buyable-open").attr('checked', 'checked');
	            } else {
	                $("#buyable-close").attr('checked', 'checked');
	            }
	            $("#buyable").attr('hidden', 'hidden');
	        }
	    }
	    $("#showable-close").click(function () {
	        $("#buyable").attr('hidden', 'hidden');
	    });
	    $("#showable-open").click(function () {
	        $("#buyable").removeAttr('hidden');
	    });
	    $('#classroom_tags').select2((_$$select = {
	
	        ajax: {
	            url: app.arguments.tagMatchUrl + '#',
	            dataType: 'json',
	            quietMillis: 100,
	            data: function data(term, page) {
	                return {
	                    q: term,
	                    page_limit: 10
	                };
	            },
	            results: function results(data) {
	                var results = [];
	                $.each(data, function (index, item) {
	
	                    results.push({
	                        id: item.name,
	                        name: item.name
	                    });
	                });
	
	                return {
	                    results: results
	                };
	            }
	        },
	        initSelection: function initSelection(element, callback) {
	            var data = [];
	            $(element.val().split(",")).each(function () {
	                data.push({
	                    id: this,
	                    name: this
	                });
	            });
	            callback(data);
	        },
	        formatSelection: function formatSelection(item) {
	            return item.name;
	        },
	        formatResult: function formatResult(item) {
	            return item.name;
	        },
	        width: 'off',
	        multiple: true,
	        maximumSelectionSize: 20,
	        placeholder: Translator.trans('classroom.manage.tag_required_hint')
	    }, _defineProperty(_$$select, "width", 'off'), _defineProperty(_$$select, "multiple", true), _defineProperty(_$$select, "createSearchChoice", function createSearchChoice() {
	        return null;
	    }), _$$select));
	};
	exports["default"] = classroomCreate();

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("5d31da2441e6b75d3a07");
	
	var $form = $('#classroom-create-form');
	
	var validator = $form.validate({
	  rules: {
	    title: {
	      required: true,
	      minlength: 2,
	      maxlength: 30
	    }
	  }
	});
	
	$form.on('click', '#classroom-create-btn', function (event) {
	  if (validator && validator.form()) {
	    $form.submit();
	  }
	});

/***/ })

});
//# sourceMappingURL=index.js.map