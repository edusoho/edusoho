import sortList from 'common/sortable';

$('.js-course-list-group').on('click', '.js-delete-btn', function () {
  cd.confirm({
    title: Translator.trans('classroom.manage.delete_course_hint_title'),
    content: Translator.trans('classroom.manage.delete_course_hint'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.cancel'),
  }).on('ok', () => {
    $.post($(this).data('url'), function (resp) {
      if (resp.success) {
        cd.message({type: 'success', message: Translator.trans('classroom.manage.delete_course_success_hint')});
        window.location.reload();
      } else {
        cd.message({type: 'danger', message: Translator.trans('classroom.manage.delete_course_fail_hint') + ':' + resp.message});
      }
    });
  });
});

sortList({
  element: '#course-list-group',
  itemSelector: 'li',
  ajax:false,
},(data)=>{
  $('#courses-form').submit();
});