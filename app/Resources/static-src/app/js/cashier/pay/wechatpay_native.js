import BasePayment from './payment';

export default class WechatPayNative extends BasePayment {
  constructor() {
    super();
    this.$container = $('body');
    this.modalID = 'wechat-qrcode-modal';
    let template = `
      <div id="${this.modalID}" class="modal">
        <div class="modal-dialog cd-modal-dialog cd-modal-dialog-sm">
          <div class="modal-content">
          
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="cd-icon cd-icon-close"></i>
              </button>
              <h4 class="modal-title">${Translator.trans('cashier.wechat_pay')}</h4>
            </div>
            
            <div class="modal-body text-center">
              <div style="height: 270px; width: 270px; margin: 0 auto;">
                <img class="cd-mb16 js-qrcode-img" src="">
              </div>
              <div class="cd-mb16">
                ${Translator.trans('cashier.wechat_pay.scan_qcode_pay_tips')}
              </div>
              <div class="cd-text-danger cd-mb32 js-pay-amount" style="font-size:16px;"></div>
            </div>
            
          </div>
        </div>
      </div>
    `;

    if (this.$container.find('#' + this.modalID).length === 0) {
      this.$container.append(template);
    }

    this.$container.find('#' + this.modalID).on('hidden.bs.modal', function() {
      clearInterval(window.intervalWechatId);
    });
  }

  afterTradeCreated(res) {
    this.checkOrderStatus();
    let $modal = this.$container.find('#' + this.modalID);
    $modal.find('.js-qrcode-img').attr('src', res.qrcodeUrl);
    $modal.find('.js-pay-amount').text('￥' + res.cash_amount);
    $modal.modal('show');
  }

  startInterval() {
    return true;
  }

}