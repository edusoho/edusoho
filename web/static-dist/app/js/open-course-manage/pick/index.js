webpackJsonp(["app/js/open-course-manage/pick/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("ede7139c79ce7ed010c2");
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var ids = [];
	var $searchForm = $('.form-search');
	
	$('#sure').on('click', function () {
	  $('#sure').button('submiting').addClass('disabled');
	
	  $.ajax({
	    type: "post",
	    url: $('#sure').data('url'),
	    data: { 'ids': ids },
	    async: false,
	    success: function success(response) {
	      if (!response['result']) {
	        (0, _notify2["default"])('danger', response['message']);
	      } else {
	        $('.modal').modal('hide');
	        window.location.reload();
	      }
	    }
	  });
	});
	
	$('#search').on('click', function () {
	
	  $.get($searchForm.data('url'), $searchForm.serialize(), function (data) {
	
	    $('#modal').html(data);
	  });
	});
	
	$('#enterSearch').keydown(function (event) {
	
	  if (event.keyCode == 13) {
	    $.get($searchForm.data('url'), $searchForm.serialize(), function (data) {
	      $('#modal').html(data);
	    });
	    return false;
	  }
	});
	
	$('#all-courses').on('click', function () {
	  $('input[name="key"]').val('');
	  $.post($(this).data('url'), $('.form-search').serialize(), function (data) {
	    $('#modal').html(data);
	  });
	});
	
	$('.row').on('click', ".course-item ", function () {
	
	  var id = $(this).data('id');
	
	  if ($(this).hasClass('enabled')) {
	    return;
	  }
	
	  if ($(this).hasClass('select')) {
	
	    $(this).removeClass('select');
	    $('.course-metas-' + id).hide();
	
	    ids = $.grep(ids, function (val, key) {
	      if (val != id) return true;
	    }, false);
	  } else {
	    $(this).addClass('select');
	    $('.course-metas-' + id).show();
	    ids.push(id);
	  }
	});

/***/ }),

/***/ "ede7139c79ce7ed010c2":
/***/ (function(module, exports) {

	"use strict";
	
	$('a[data-role="pick-modal"]').click(function () {
	  $("#modal").html("");
	  $("#modal").load($(this).data('url'));
	});

/***/ })

});
//# sourceMappingURL=index.js.map