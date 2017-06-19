webpackJsonp(["app/js/group/background-crop/index"],[
/* 0 */
/***/ (function(module, exports) {

	import EsImageCrop from 'common/es-image-crop.js';
	var imageCrop = new EsImageCrop({
	  element: "#logo-crop",
	  group: 'group',
	  cropedWidth: 1140,
	  cropedHeight: 150
	});
	
	imageCrop.afterCrop = function (response) {
	  var url = $("#upload-picture-btn").data("url");
	  $.post(url, { images: response }, function () {
	    document.location.href = $("#upload-picture-btn").data("reloadUrl");
	  });
	};
	
	$("#upload-picture-btn").click(function (e) {
	  e.stopPropagation();
	  imageCrop.crop({
	    imgs: {
	      backgroundLogo: [1140, 150]
	    }
	  });
	});

/***/ })
]);