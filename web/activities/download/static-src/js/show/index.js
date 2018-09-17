import ActivityEmitter from 'app/js/activity/activity-emitter';
let emitter = new ActivityEmitter();

$('.download-activity-list').on('click', 'a', function () {
  $(this).attr('href', $(this).data('url'));
  emitter.emit('finish', {fileId: $(this).data('fileId')});
});
$('#download-activity').perfectScrollbar();
$('#download-activity').perfectScrollbar('update');