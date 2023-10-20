new class NonAdministrator {
  constructor() {
    this.$modal = $('#modal');
    this.$closeBtn = $('.js-close-modal')
    this.init();
  }

  init() {
    this.clickCloseBtn()
  }

  clickCloseBtn() {
    this.$closeBtn.on('click', ()=> {
      this.$modal.modal('hide');
    })
  }
}