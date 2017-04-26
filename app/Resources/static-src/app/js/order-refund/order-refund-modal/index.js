let $form = $('#refund-form');
let $modal = $form.parents('.modal');

$form.find('[name="reason[type]"]').on('change', function () {
  let $this = $(this),
    reasonType = $this.val();

  if (reasonType == 'other') {
    $form.find('[name="reason[note]"]').val('').show();
  } else {
    let reason = $this.find('option[value=' + reasonType + ']').text();
    $form.find('[name="reason[note]"]').hide().val(reason);
  }
  $form.find('.warnning').text('');
}).change();

$form.find('[name="reason[note]"]').on('change', function () {
  if ($form.find('[name="reason[note]"]').val().length > 120) {
    $form.find('.warnning').text(Translator.trans('order.refund.reason_limit_hint'));
  } else if ($form.find('[name="reason[note]"]').val().length == 0) {
    $form.find('.warnning').text(Translator.trans('order.refund.reason_required_hint'));
  } else {
    $form.find('.warnning').text('');
  }
}).change();

$form.on('submit', function () {
  if ($form.find('[name="reason[type]"]').val() == 'reason') {
    $form.find('.warnning').text(Translator.trans('order.refund.reason_choose_hint'));
  } else if ($form.find('[name="reason[note]"]').val().length > 120) {
    $form.find('.warnning').text(Translator.trans('order.refund.reason_limit_hint'));
  } else if ($form.find('[name="reason[note]"]').val().length == 0) {
    $form.find('.warnning').text(Translator.trans('order.refund.reason_required_hint'));
  } else {
    $modal.find('[type=submit]').button('loading');
    $.post($form.attr('action'), $form.serialize(), function (response) {
      window.location.reload();
    }, 'json');
  }
  return false;
});
