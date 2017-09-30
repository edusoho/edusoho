import BasePayment from './BasePayment';

export default class WechatPayNative extends BasePayment {
  $container = $('body');

  modalID = 'wechat-qrcode-modal';

  constructor() {
    super();

    let template = `
      <div id="${this.modalID}" class="modal">
        <div class="modal-dialog cd-modal-dialog">
          <div class="modal-content">
          
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="cd-icon cd-icon-close"></i>
              </button>
              <h4 class="modal-title">${Translator.trans('cashier.wechat_pay')}</h4>
            </div>
            
            <div class="modal-body">
              <div class="qrcode-img">
                <img class = 'img-responsive js-qrcode-img' src="">
                  <div class="text-qrcode hidden-xs">
                    ${Translator.trans('cashier.wechat_pay.scan_qcode_pay_tips')}
                  </div>
                <span class="pay-rmb js-pay-amount"></span>
              </div>
            </div>
            
          </div>
        </div>
      </div>
     
    `;

    if (this.$container.find('#'+this.modalID).length === 0) {
      this.$container.append(template);
    }

    this.$container.find('#'+this.modalID).on('hidden.bs.modal', function () {
      clearInterval(window.intervalWechatId);
    });
  }

 pay(params) {
   BasePayment.createTrade(params, this.callback.bind(this));
 }

 callback(res) {
   let $modal = this.$container.find('#'+this.modalID);
   $modal.find('.js-qrcode-img').attr('src', res.qrcodeUrl);
   $modal.find('.js-pay-amount').text(res.cash_amount);
   $modal.modal('show');
   this.startInterval(res.tradeSn);
 }

 startInterval(tradeSn) {
   window.intervalWechatId = setInterval(this.checkIsPaid.bind(this, tradeSn), 2000);
 }

 checkIsPaid(tradeSn) {
   BasePayment.getTrade(tradeSn, res => {
     if (res.isPaid) {
       location.href = res.successUrl;
     }

   });
 }
}
