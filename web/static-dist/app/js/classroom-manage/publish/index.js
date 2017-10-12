webpackJsonp(["app/js/classroom-manage/publish/index"],[
/* 0 */
/***/ (function(module, exports) {

	"use strict";
	
	$("#publishSure").on("click", function () {
	    $('#publishSure').button('submiting').addClass('disabled');
	    $.post($("#publishSure").data("url"), function (html) {
	        $("#modal").modal('hide');
	        window.location.reload();
	    }).error(function () {});
	});

/***/ })
]);
//# sourceMappingURL=index.js.map