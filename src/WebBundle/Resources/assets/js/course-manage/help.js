import notify from 'common/notify';

export const deleteTask = ()=> {
  $('body').on('click','.delete-item',function(evt){
    if (!confirm(Translator.trans('是否确定删除任务？')))
      return;
    $.post($(evt.target).data('url'), function(data) {
      console.log(data);
      if (data.success) {
        notify('success', "删除成功");
        location.reload();
      } else{
        notify('danger', "删除失败");
      }
    });
  });
}


