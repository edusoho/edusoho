import notify from 'common/notify';

let $modal = $('#modal');
$modal.on('click', '.js-close-btn', event => {
  $modal.modal('hide');
});

$('.js-use').on('click', e => {
  let $target = $(e.currentTarget);
  $.post($target.data('url'), {feature: $target.data('feature')}, resp => {});
  window.open($target.data('openUrl'));
});

$('.js-like').on('click', e => {
  notify('success', '已收到您的反馈');
  let $target = $(e.currentTarget);
  let $img = $target.find('.ai-survey-content-center-btn-image');
  $img[0].src = '/static-dist/app/img/admin-v2/already-collect-img.png';
  $.post($target.data('url'), {feature: $target.data('feature')}, resp => {});
});

$('.js-apply').on('click', e => {
  let $target = $(e.currentTarget);
  let $applyModal = $('#attachment-modal');
  $applyModal.load($target.data('url'));
  $applyModal.modal('show');
  $modal.modal('hide');
});
