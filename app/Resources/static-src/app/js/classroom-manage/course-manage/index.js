import sortList from 'common/sortable';
import notify from 'common/notify';

$('.course-list-group').on('click', '.close', function () {
  if (confirm(Translator.trans('classroom.manage.delete_course_hint'))) {
    $.post($(this).data('url'), function (resp) {
      if (resp.success) {
        notify('success',Translator.trans('classroom.manage.delete_course_success_hint'));
        window.location.reload();
      } else {
        notify('danger',Translator.trans('classroom.manage.delete_course_fail_hint') + ':' + resp.message);
      }
    });
  }
});

sortList({
  element: '#course-list-group',
  itemSelector: 'li',
  ajax:false,
},(data)=>{
  $('#courses-form').submit();
});