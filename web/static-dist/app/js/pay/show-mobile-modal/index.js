webpackJsonp(["app/js/pay/show-mobile-modal/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $modal = $('#modal');
	var $form = $('#unbind-form');
	var $btn = $('#unbind-btn');
	
	var validator = $form.validate({
	  rules: {
	    mobile: {
	      required: true,
	      phone: true
	    }
	  }
	});
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('loading');
	    $modal.modal('hide');
	    var payAgreementId = $("input[name='payAgreementId']").val();
	    $.post($form.attr('action'), $form.serialize(), function (response) {
	      if (response.success) {
	        $('#unbind-bank-' + payAgreementId).remove();
	        Notify.success(response.message);
	      } else {
	        Notify.danger(response.message);
	      }
	    });
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map