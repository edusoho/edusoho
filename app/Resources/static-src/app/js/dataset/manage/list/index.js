import notify from 'common/notify';
$(function () {
  var $table = $('#dataset-table');
  $table.on('click', '.delete-dataset', function () {
    var $this = $(this);
    if (!confirm("是否删除此数据集"))
      return;
    var $tr = $this.parents('tr');
    $.post($this.data('url'), function (data) {
      if (data.status.code != 2000000) {
        notify('danger', data.status.message);
      } else {
        $tr.remove();
        notify('success', ("删除成功"));
      }
    });
  });
})