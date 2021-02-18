import notify from 'common/notify';
$(function(){
    var $table = $('#images-table');
    $table.on('click', '.delete-course', function() {
        var $this = $(this);
        if (!confirm("是否删除此镜像"))
          return;
        var $tr = $this.parents('tr');
        $.post($this.data('url'), function(data) {
          if (data.code > 0) {
            notify('danger', data.message);
          } else if (data.code == 0) {
            $tr.remove();
            notify('success',(data.message));
          } else {
            $('#modal').modal('show').html(data);
          }
        });
      });
})