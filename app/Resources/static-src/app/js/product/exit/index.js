let $form = $('#refund-form');
let $modal = $form.parents('.modal');
let $reasonNote = $form.find('#reasonNote');
let $warnning = $form.find('.warnning');
const $submitBtn = $('button[type="submit"]')

$form.find('[name="reason[type]"]').on('change', function () {
  let $this = $(this),
  $selected = $this.find('option:selected');

  if ($selected.val() == 'other') {
    $reasonNote.val('').removeClass('hide');
    $submitBtn.attr('disabled', true)
  } else {
    $reasonNote.addClass('hide').val($selected.text());
    if ($selected.val() !== 'reason') {
      $submitBtn.removeAttr('disabled')
    } else {
      $submitBtn.attr('disabled', true)
    }
  }
  $warnning.text('');
})

$reasonNote.on('change', function () {
  let $this = $(this);

  if ($this.val().length == 0) {
    $warnning.text(Translator.trans('order.refund.reason_required_hint'));
  } else {
    $warnning.text('');
  }

  if ($form.find('[name="reason[type]"] option:selected').val() === 'other') {
    if ($this.val().length == 0) {
      $submitBtn.attr('disabled', true)
    } else {
      $submitBtn.removeAttr('disabled')
    }
  }
})

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

  $modal.find('[type=submit]').button('loading').attr('disabled', true);
});
