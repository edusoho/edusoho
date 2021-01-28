import BatchCreate from 'app/js/course-manage/batch-create/batch-create.js';

new BatchCreate({
  element: '#uploader-container',
});

let $el = $('#uploader-container');

$el.parents('.modal').on('hidden.bs.modal', () => {
  window.location.reload();
});