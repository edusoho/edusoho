import notify from 'common/notify';
$(function () {
  var $table = $('#images-table');
  $table.on('click', '.delete-course', function () {
    var $this = $(this);
    if (!confirm("是否删除此镜像"))
      return;
    var $tr = $this.parents('tr');
    $.post($this.data('url'), function (data) {
      if (data.status.code != 2000000) {
        notify('danger', data.status.message);
      } else {
        $tr.remove();
        notify('success', (data.status.message));
      }
    });
  });
})