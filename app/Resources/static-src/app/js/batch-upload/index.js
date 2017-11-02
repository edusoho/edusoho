import BatchCreate from 'app/js/course-manage/batch-create/batch-create.js';

new BatchCreate({
  element: '#batch-uploader',
})

let $el = $('#batch-uploader');

$el.parents('.modal').on('hidden.bs.modal', () => {
  window.location.reload();
});