import sortList from 'common/sortable';
import notify from 'common/notify';

let courseHandle = {
  remove: function (e) {
    let url = $(e).data('url');

    $.post(url, function (resp) {
      if (resp.success) {
        cd.message({type: 'success', message: Translator.trans('classroom.manage.delete_course_success_hint')});
        window.location.reload();
      } else {
        cd.message({type: 'danger', message: Translator.trans('classroom.manage.delete_course_fail_hint') + ':' + resp.message});
      }
    });
  },
  del: function (e) {
    const _this = this;
    let url = $(e).data('del-url');
    let id = $(e).data('id');

    $.post(url + '?jsonp=checkPasswordJsonp('+id+')', function (resp) {
      if (resp.code != undefined) {
        _this.delAjaxCallback(resp);
        return;
      }

      $('#modal').modal('show').html(resp);
    });
  },
  delAjaxCallback: function (resp) {
    if (resp.code == 0) {
      notify('success', Translator.trans('admin.course.delete_success_hint'));
      location.reload();
    } else {
      notify('success', Translator.trans('admin.course.delete_failed_hint') + '：' + resp.message);
    }
  }
}

document.checkPasswordJsonp = function(id) {
  $.post($('.js-course-list-group .js-delete-btn[data-id=' + id + ']').data('del-url'), function (resp) {
    courseHandle.delAjaxCallback(resp);
  });
}


$('.js-course-list-group').on('click', '.js-delete-btn', function () {
  const _this = this;
  let content = Translator.trans('classroom.manage.delete_course_hint');
  let adminContent = `
    <br/><br/>
    <label for="is_delete" style="font-weight: normal">
        <input type="checkbox" name="is_delete" id="is_delete" value="1">&nbsp;${Translator.trans('classroom.manage.admin_delete_course_set_hint')}
    </label>
  `;

  if ($(this).data('del-url')) {
    content += adminContent;
  }
  cd.confirm({
    title: Translator.trans('classroom.manage.delete_course_hint_title'),
    content: content,
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.cancel'),
    className: 'js-adapt-confirm-width',
  }).on('ok', () => {
    if ($('#is_delete').is(':checked')) {
      courseHandle.del(_this);
      return;
    }
    courseHandle.remove(_this);
  });

  if ($(this).data('del-url')) {
    $('.js-adapt-confirm-width').find('.cd-modal-dialog-sm').removeClass('cd-modal-dialog-sm').addClass('cd-modal-dialog-md');
  }
});

sortList({
  element: '#course-list-group',
  itemSelector: 'li',
  ajax:false,
},(data)=>{
  $.post($('#course-list-group').data('sortUrl'), $('#courses-form').serialize(), (response) => {});
});