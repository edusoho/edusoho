webpackJsonp(["app/js/pay/select/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $modal = $('#modal');
	
	$(".form-paytype").on('click', '.check', function () {
	  var $this = $(this);
	  if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
	    $this.addClass('active').siblings().removeClass('active');
	    $("input[name='payment']").val($this.attr("id"));
	  }
	  if ($this.attr('id') == 'quickpay') {
	    $('.js-pay-agreement').show();
	  } else {
	    $('.js-pay-agreement').hide();
	  }
	}).on('click', '.js-order-cancel', function () {
	  var $this = $(this);
	  $.post($this.data('url'), function (data) {
	    if (data != true) {
	      (0, _notify2["default"])('danger', Translator.trans('notify.order_cancel_failed.message'));
	    }
	    (0, _notify2["default"])('success', Translator.trans('notify.order_cancel_succeed.message'));
	    window.location.href = $this.data('goto');
	  });
	}).on("click", '.js-pay-bank', function (e) {
	  e.stopPropagation();
	  var $this = $(this);
	  $this.addClass('checked').siblings('li').removeClass('checked');
	  $this.find('input').prop("checked", true);
	}).on('click', '.js-pay-bank .closed', function () {
	
	  if (!confirm(Translator.trans('confirm.bind_pay_bank.message'))) {
	    return;
	  }
	
	  var $this = $(this);
	  var payAgreementId = $this.closest(".js-pay-bank").find("input").val();
	
	  $.post($this.data('url'), { 'payAgreementId': payAgreementId }, function (response) {
	    if (response.success == false) {
	      (0, _notify2["default"])('danger', response.message);
	    } else {
	      $modal.modal('show');
	      $modal.html(response);
	    }
	  });
	});
	
	$("input[name='payment']").val($('div .active').attr("id"));
	
	$("#copy").on('click', function (event) {
	  var textarea = document.createElement("textarea");
	  textarea.style.position = 'fixed';
	  textarea.style.top = 0;
	  textarea.style.left = 0;
	  textarea.style.border = 'none';
	  textarea.style.outline = 'none';
	  textarea.style.resize = 'none';
	  textarea.style.background = 'transparent';
	  textarea.style.color = 'transparent';
	
	  textarea.value = document.location.href;
	  var ele = $(textarea);
	  $(this).append(ele);
	
	  textarea.select();
	  document.execCommand('copy');
	
	  ele.remove();
	  (0, _notify2["default"])('success', Translator.trans('notify.copy_succeed.message'));
	});

/***/ })
]);
//# sourceMappingURL=index.js.map