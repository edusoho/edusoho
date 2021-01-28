import sortList from 'common/sortable';

$('.js-course-list-group').on('click', '.js-delete-btn',  (event) => {
  const $target = $(event.target);
  const courseId = $target.data('id');
  $.post($target.data('cancelUrl')).done(() => {
    $('.item-' + courseId).remove();
  });
});

sortList({
  element: '#course-list-group',
  itemSelector: 'li.course-item',
  ajax: false
});



