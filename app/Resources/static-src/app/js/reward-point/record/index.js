class PopoverManage {
  constructor(props) {
    this.$elems = $(props.selector);
    this.currentElem = null;
    this.init();
  }

  init() {

    this.initEvent();
  }

  initEvent() {
    this.$elems.on('click', (e) => {
      this.onClick(e);
    });
  }

  onClick(e) {
    if (this.currentElem == e.currentTarget) {
      $(e.currentTarget).popover('hide');
      this.currentElem = null;
    } else {
      $(e.currentTarget).popover('show');
      $(this.currentElem).popover('hide');
      this.currentElem =  e.currentTarget;
    }
  }
}

new PopoverManage({selector: '.js-popover'});