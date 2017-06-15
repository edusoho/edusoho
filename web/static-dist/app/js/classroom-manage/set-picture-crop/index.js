webpackJsonp(["app/js/classroom-manage/set-picture-crop/index"],[
/* 0 */
/***/ (function(module, exports) {

	import EsImageCrop from 'common/es-image-crop.js';
	
	var imageCrop = new EsImageCrop({
	    element: "#classroom-picture-crop",
	    group: "course",
	    cropedWidth: 525,
	    cropedHeight: 350
	});
	
	imageCrop.afterCrop = function (response) {
	    var url = $("#upload-picture-btn").data("url");
	    $.post(url, { images: response }, function () {
	        document.location.href = $("#upload-picture-btn").data("gotoUrl");
	    });
	};
	
	$("#upload-picture-btn").click(function (e) {
	    e.stopPropagation();
	    imageCrop.crop({
	        imgs: {
	            large: [525, 350],
	            middle: [345, 230],
	            small: [213, 142]
	        }
	    });
	});
	
	$('.go-back').click(function () {
	    history.go(-1);
	});

/***/ })
]);