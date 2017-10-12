webpackJsonp(["app/js/material-lib/share-history/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $table = $('#share-history-table');
	
	// tab 切换
	// import '../share-form';
	$('.js-share-tab').on('click', function () {
	  var $this = $(this);
	
	  // if ($this.hasClass('active')) {
	  //   return;
	  // }
	
	  $.get($this.data('url'), function (html) {
	    $table.html(html);
	  });
	
	  $this.parent().addClass('active').siblings().removeClass('active');
	});
	
	// 取消分享
	$table.on('click', '.cancel-share-btn', function (e) {
	  var $btn = $(e.currentTarget);
	  var $this = $(this);
	
	  $.post($this.data('url'), {
	    targetUserId: $this.attr('targetUserId')
	  }, function (response) {
	    $btn.closest('.share-history-record').remove();
	    (0, _notify2["default"])('success', Translator.trans('material.cancel_share.tips'));
	  }, 'json');
	});
	
	$('.modal').off('click.modal-pagination');
	$table.on('click', '.pagination li', function () {
	  var $this = $(this);
	  var page = $this.data('page');
	  var url = $this.closest(".pagination").data('url');
	
	  $.get(url, { 'page': page }, function (html) {
	    $table.html(html);
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map