export default class TurnIntoStudent {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $('.js-turn-into-student').on('click', event => {
      cd.confirm({
        title: "<i class='cd-icon cd-icon-warning-o' style='color: orange;display: inline-block;margin-right: 10px;'></i>恢复学员身份?",
        content: "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;恢复后员工身份将取消",
        okText: "确定",
        cancelText: '取消',
        className: '',
      }).on('ok', () => {
        const $target = $(event.currentTarget);
        $.post($target.data('url'), resp => {
          if (resp.success) {
            $target.closest('tr').remove();
            cd.message({
              type: 'success',
              message: '学员身份恢复成功'
            });
          }
        });
      }).on('cancel', () => {

      });
    })

  }
}

new TurnIntoStudent();