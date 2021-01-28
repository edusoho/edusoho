class CloudSearch {
  constructor(options) {
    this.$element = $(options.element);
    this.init();
  }

  init() {
    if(this.$element.find('#search-input-group .form-control').val()) {
      this.$element.find('.js-btn-clear').show();
    }
    this.initEvent();
  }
  
  initEvent() {
    this.$element.on('click', '.js-btn-clear', event => this.onBtnClear(event));
    this.$element.on('input propertychange', '#search-input-group .form-control', event => this.onSearchInput(event));
  }

  onBtnClear(event) {
    let $this = $(event.currentTarget);
    $this.siblings('input').val('').end().hide();
  }

  onSearchInput(event) {
    let $this = $(event.currentTarget);
    let btnClear = $this.siblings('.js-btn-clear');

    if ($this.val()) {
      btnClear.show();
    } else {
      btnClear.hide();
    }
  }
}

export default CloudSearch;