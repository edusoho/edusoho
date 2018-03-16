import sortList from 'common/sortable';

$('.course-list-group').on('click', '.close', function () {
  let recommendId = $(this).data('recommendId');
  let courseId = $(this).data('id');
  $.post($(this).data('cancelUrl')).done(function () {

    $('.item-' + courseId).remove();
  });
});

sortList({
  element: '.course-list-group',
  itemSelector: 'li.course-item',
  ajax: false
});



