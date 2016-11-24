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

export const sortList = () => {
  var $list = $("#sortable-list").sortable({
    distance: 20,
    itemSelector: 'li.drag',
    onDrop: function (item, container, _super) {
      _super(item, container);
      var data = $list.sortable("serialize").get();
      console.log(data);
      //排序URL
      $.post($list.data('sortUrl'), {ids:data}, function(response){
        document.location.reload();
      });
    },
    serialize: function(parent, children, isContainer) {
      return isContainer ? children : parent.attr('id');
    },
  });
}


export default {
  deleteTask,
  sortList
}

