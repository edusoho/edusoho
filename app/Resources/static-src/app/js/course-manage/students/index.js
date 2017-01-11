import notify from 'common/notify';

class Students {
  constructor() {
    this.init();
  }

  init() {

    $("#refund-coin-tips").popover({
        html: true,
        trigger: 'hover',//'hover','click'
        placement: 'left',//'bottom',
        content: $("#refund-coin-tips-html").html()
    });
    
    $('body').on('click', '.js-remove-student', function(evt) {
      if (!confirm(Translator.trans('是否确定删除该学员？'))) {
        return;
      }
      $.post($(evt.target).data('url'), function(data) {
        if (data.success) {
          notify('success', '移除成功');
          location.reload();
        } else {
          notify('danger', '移除失败：' + data.message);
        }
      });
    });
  }
}

new Students();
