export default class Face {
  constructor(options) {
    this.options = options;
    this.$element = this.options.element;
    this.$loginDom = $('.js-sts-login');
    this.init();
  }
  init() {
    this.bindEvent();
    this.closeModal();
  }

  bindEvent() {
    this.$loginDom.on('click', '.js-login-back', () => this.back());
    this.$loginDom.on('click', '.js-invalid-btn', () => this.refresh());
    this.$loginDom.on('click', '.js-approve-again-btn', () => this.approveAgain());
    this.$element.on('click', '.js-sts-login-link', () => this.showQrcode());
  }

  back() {
    $('.js-approve-ing, .js-feedback, .js-sts-login').addClass('hidden');
    if (this.options.target) {
      this.$element.find(this.options.target).removeClass('hidden');
    } else {
      this.$element.removeClass('hidden');
    }
  }

  showQrcode() {
    $.ajax({
      type: 'post',
      url: this.$loginDom.data('url'),
      dataType: 'json',
      success: (data) => {
        console.log(data);
        this.$loginDom.find('.js-sts-login-qrcode img').attr('src', data.qrcode);
        this.onlyShow();
        this.token = data.token;
        this.pollStatus();
      }
    });
  }

  approveAgain() {
    $('.js-feedback').addClass('hidden');
    $('.js-login-section, .js-sts-tip').removeClass('hidden');
    this.refresh();
  }

  refresh() {
    $('.js-approve-ing').addClass('hidden');
    this.showQrcode();
  }

  pollStatus() {
    const self = this;
    const token = this.token;
    const goto = this.$loginDom.data('goto');
    const $failStatus = $('.js-fail-feedback');
    const $successStatus = $('.js-success-feedback');
    const $failTip = $('.js-fail-tip');
    const $failBtn = $('.js-approve-again-btn');
    if (this.$loginDom.hasClass('hidden') || this.flag) {
      return;
    }
    $.get(`/login/face_token/${token}`, { goto: goto }, (res) => {
      switch (res.status) {
      case 'created':
        setTimeout(() => {
          self.pollStatus();
        }, 2000);
        break;

      case 'processing':
        $('.js-invalid-btn').addClass('hidden');
        $('.js-approve-ing, .js-approve-ing-tip').removeClass('hidden');
        setTimeout(() => {
          self.pollStatus();
        }, 2000);
        break;

      case 'expired':
        $('.js-approve-ing-tip').addClass('hidden');
        $('.js-approve-ing, .js-invalid-btn').removeClass('hidden');
        break;

      case 'successed':
        self.statusShow();
        $successStatus.removeClass('hidden').siblings().addClass('hidden');
        window.location.href = res.url;
        break;

      case 'failed':
        self.feedbackShow($failStatus, $failBtn, $failTip);
        break;

      case 'failures':
        self.feedbackShow($failStatus, $failTip, $failBtn);
        break;
      }
    });
  }

  closeModal() {
    $('#login-modal').on('hide.bs.modal', () => {
      this.flag = true;
    });
  }

  onlyShow() {
    $('.js-sts-login, .js-login-section, .js-sts-tip').removeClass('hidden');
    if (this.options.target) {
      this.$element.find(this.options.target).addClass('hidden');
    } else {
      this.$element.addClass('hidden');
    }
  }

  statusShow() {
    $('.js-login-section, .js-sts-tip').addClass('hidden');
    $('.js-feedback').removeClass('hidden');
  }

  feedbackShow($status, $show, $hide) {
    this.statusShow();
    $status.removeClass('hidden').siblings().addClass('hidden');
    $show.removeClass('hidden');
    $hide.addClass('hidden');
  }
}
