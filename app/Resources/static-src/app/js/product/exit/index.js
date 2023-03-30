let $form = $('#refund-form');
let $modal = $form.parents('.modal');
let $reasonNote = $form.find('#reasonNote');
const $reasonNoteContainer = $form.find('#reasonNote-container');
let $warnning = $form.find('.warnning');
const $reasonNoteNumber = $form.find('.js-textarea-number')
const $submitBtn = $('button[type="submit"]')

function changeReasonNote(text = '') {
  $reasonNote.val(text)
  $reasonNoteNumber.text(text.length)
}

$form.find('[name="reason[type]"]').on('change', function () {
  let $this = $(this),
  $selected = $this.find('option:selected');

  if ($selected.val() == 'other') {
    changeReasonNote('')
    $reasonNoteContainer.removeClass('hide');
    $submitBtn.attr('disabled', true)
  } else {
    $reasonNoteContainer.addClass('hide')
    changeReasonNote($selected.text())
    
    if ($selected.val() !== '') {
      $submitBtn.removeAttr('disabled')
    } else {
      $submitBtn.attr('disabled', true)
    }
  }
  $warnning.text('');
})

$reasonNote.on('input', function () {
  $reasonNoteNumber.text($reasonNote.val().length)

  if ($reasonNote.val().length == 0) {
    $submitBtn.attr('disabled', true)
  } else {
    $submitBtn.removeAttr('disabled')
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
