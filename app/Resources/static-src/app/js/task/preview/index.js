import { buyBtn } from 'app/common/widget/btn-util';

buyBtn($('.js-task-preview-buy-btn'));

$('#modal').on('hidden.bs.modal', function () {
  $('#viewerIframe').attr('src', '');
});
$('#js-buy-btn').on('click', function () {
  $('#modal').modal('hide');
});

function postCoursePreviewEvent()
{
  let $obj = $('#modal-event-report');
  let postData = $obj.data();
  $.post($obj.data('url'), postData);
}

postCoursePreviewEvent();