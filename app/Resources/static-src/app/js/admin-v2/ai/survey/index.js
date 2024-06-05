import notify from 'common/notify';

let $modal = $('#modal');
$modal.on('click', '.js-close-btn', event => {
  $modal.modal('hide');
});

let $Like = $('.js-like')
$Like.on('click',function(e) {
  notify('success', '已收到您的反馈');
  let $target = $(e.currentTarget);
  let $img = $target.find('.ai-survey-content-center-btn-image');
  $img[0].src = "/static-dist/app/img/admin-v2/already-collect-img.png"
  $Like.load($target.data('url'));
})

let $Apply = $('.js-apply')
$Apply.on('click',function(e) {
  let  $target = $(e.currentTarget);
  let $applyModal = $('#attachment-modal');
  $applyModal.load($target.data('url'));
  $applyModal.modal('show');
  $modal.modal('hide');
})
