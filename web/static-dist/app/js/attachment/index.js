webpackJsonp(["app/js/attachment/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $modal = $('#attachment-modal');
	var $uploader = $modal.find('#uploader-container');
	
	var uploader = new UploaderSDK({
	  id: $uploader.attr('id'),
	  initUrl: $uploader.data('initUrl'),
	  finishUrl: $uploader.data('finishUrl'),
	  accept: $uploader.data('accept'),
	  process: $uploader.data('process'),
	  fileSingleSizeLimit: $uploader.data('fileSingleSizeLimit'),
	  ui: 'single',
	  locale: document.documentElement.lang
	});
	
	uploader.on('error', function (type) {
	  (0, _notify2["default"])('danger', type.message);
	});
	
	uploader.on('file.finish', function (file) {
	  if (file.length && file.length > 0) {
	    var minute = parseInt(file.length / 60);
	    var second = Math.round(file.length % 60);
	    $("#minute").val(minute);
	    $("#second").val(second);
	    $("#length").val(minute * 60 + second);
	  }
	
	  var $metas = $('[data-role="metas"]');
	  var currentTarget = $metas.data('currentTarget');
	
	  var $ids = $('.' + $metas.data('idsClass'));
	  var $list = $('.' + $metas.data('listClass'));
	  if (currentTarget != '') {
	    $ids = $('[data-role=' + currentTarget + ']').find('.' + $metas.data('idsClass'));
	    $list = $('[data-role=' + currentTarget + ']').find('.' + $metas.data('listClass'));
	  }
	
	  $.get('/attachment/file/' + file.id + '/show', function (html) {
	    $list.append(html);
	    $ids.val(file.id);
	    $modal.modal('hide');
	    $list.siblings('.js-upload-file').hide();
	  });
	});
	
	//只执行一次
	$modal.one('hide.bs.modal', function (event) {
	  uploader.destroy();
	  uploader = null;
	});

/***/ })
]);
//# sourceMappingURL=index.js.map