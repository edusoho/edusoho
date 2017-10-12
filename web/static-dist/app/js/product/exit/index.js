webpackJsonp(["app/js/product/exit/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#refund-form');
	var $modal = $form.parents('.modal');
	var $reasonNote = $form.find('#reasonNote');
	var $warnning = $form.find('.warnning');
	
	$form.find('[name="reason[type]"]').on('change', function () {
	  var $this = $(this),
	      $selected = $this.find('option:selected');
	  if ($selected.val() == 'other') {
	    $reasonNote.val('').removeClass('hide');
	  } else {
	    $reasonNote.addClass('hide').val($selected.text());
	  }
	  $warnning.text('');
	}).change();
	
	$reasonNote.on('change', function () {
	  var $this = $(this);
	  if ($this.val().length > 120) {
	    $warnning.text(Translator.trans('order.refund.reason_limit_hint'));
	  } else if ($this.val().length == 0) {
	    $warnning.text(Translator.trans('order.refund.reason_required_hint'));
	  } else {
	    $warnning.text('');
	  }
	}).change();
	
	$form.on('submit', function () {
	  if ($form.find('#reasonType').val() == 'reason') {
	    $warnning.text(Translator.trans('order.refund.reason_choose_hint'));
	    return false;
	  } else if ($reasonNote.val().length > 120) {
	    $warnning.text(Translator.trans('order.refund.reason_limit_hint'));
	    return false;
	  } else if ($reasonNote.val().length == 0) {
	    $warnning.text(Translator.trans('order.refund.reason_required_hint'));
	    return false;
	  }
	
	  $modal.find('[type=submit]').button('loading').attr("disabled", true);
	});

/***/ })
]);
//# sourceMappingURL=index.js.map