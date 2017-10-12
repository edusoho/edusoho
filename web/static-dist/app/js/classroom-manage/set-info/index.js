webpackJsonp(["app/js/classroom-manage/set-info/index"],{

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
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	__webpack_require__("5d31da2441e6b75d3a07");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	initEditor();
	var validator = initValidator();
	toggleExpiryValue($("[name=expiryMode]:checked").val());
	
	$("[name='expiryMode']").change(function () {
	  if (app.arguments.classroomStatus === 'published') {
	    return false;
	  }
	  var expiryValue = $("[name='expiryValue']").val();
	  if (expiryValue) {
	    if (expiryValue.match("-")) {
	      $("[name='expiryValue']").data('date', $("[name='expiryValue']").val());
	    } else {
	      $("[name='expiryValue']").data('days', $("[name='expiryValue']").val());
	    }
	    $("[name='expiryValue']").val('');
	  }
	
	  if ($(this).val() == 'forever') {
	    $('.expiry-value-js').addClass('hidden');
	  } else {
	    $('.expiry-value-js').removeClass('hidden');
	    var $esBlock = $('.expiry-value-js > .controls > .help-block');
	    $esBlock.text($esBlock.data($(this).val()));
	  }
	  toggleExpiryValue($(this).val());
	});
	
	function initEditor() {
	  var editor_classroom_about = CKEDITOR.replace('about', {
	    allowedContent: true,
	    toolbar: 'Detail',
	    filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
	    filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
	  });
	
	  $('[name="categoryId"]').select2({
	    treeview: true,
	    dropdownAutoWidth: true,
	    treeviewInitState: 'collapsed',
	    placeholderOption: 'first'
	  });
	}
	
	function initValidator() {
	  return $('#classroom-set-form').validate({
	    rules: {
	      title: {
	        required: true
	      }
	    }
	  });
	
	  $('#classroom-save').click(function () {
	    // validator.form();
	  });
	}
	
	function toggleExpiryValue(expiryMode) {
	  if (!$("[name='expiryValue']").val()) {
	    $("[name='expiryValue']").val($("[name='expiryValue']").data(expiryMode));
	  }
	  elementRemoveRules($("[name='expiryValue']"));
	  switch (expiryMode) {
	    case 'days':
	      $('[name="expiryValue"]').datetimepicker('remove');
	      $(".expiry-value-js .controls > span").removeClass('hidden');
	      elementAddRules($('[name="expiryValue"]'), getExpiryModeDaysRules());
	      validator.form();
	      break;
	    case 'date':
	      if ($('#classroom_expiryValue').attr('readonly') !== undefined) {
	        return false;
	      }
	      $(".expiry-value-js .controls > span").addClass('hidden');
	      $("#classroom_expiryValue").datetimepicker({
	        language: document.documentElement.lang,
	        autoclose: true,
	        format: 'yyyy-mm-dd',
	        minView: 'month',
	        endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
	      });
	      $("#classroom_expiryValue").datetimepicker('setStartDate', new Date());
	      elementAddRules($('[name="expiryValue"]'), getExpiryModeDateRules());
	      validator.form();
	      break;
	    default:
	      break;
	  }
	}
	
	function getExpiryModeDaysRules() {
	  return {
	    required: true,
	    digits: true,
	    min: 1,
	    max: 10000,
	    messages: {
	      required: Translator.trans('classroom.manage.expiry_mode_days_error_hint')
	    }
	  };
	}
	
	function getExpiryModeDateRules() {
	  return {
	    required: true,
	    date: true,
	    after_now_date: true,
	    messages: {
	      required: Translator.trans('classroom.manage.expiry_mode_date_error_hint')
	    }
	  };
	}
	
	function elementAddRules($element, options) {
	  $element.rules("add", options);
	}
	
	function elementRemoveRules($element) {
	  $element.rules('remove');
	}

/***/ })

});
//# sourceMappingURL=index.js.map