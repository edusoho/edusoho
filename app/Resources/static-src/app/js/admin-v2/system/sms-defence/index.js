export default class UnlockBlackIp {
  constructor() {
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $('.unlockIp').on('click', event => {
      cd.confirm({
        title: '<i class=\'cd-icon cd-icon-warning-o\' style=\'color: orange;display: inline-block;margin-right: 10px;\'></i>解封IP?',
        content: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;解封后该IP可以正常发送短信',
        okText: '确定',
        cancelText: '取消',
        className: '',
      }).on('ok', () => {
        const $target = $(event.currentTarget);
        $.post($target.data('url'), resp => {
          if (resp.success) {
            $target.closest('tr').remove();
            cd.message({
              type: 'success',
              message: 'IP解封成功'
            });
          }
        });
      }).on('cancel', () => {

      });
    });

  }
}
new UnlockBlackIp();