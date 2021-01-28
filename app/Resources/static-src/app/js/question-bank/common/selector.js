export default class Selector {
  constructor(selector) {
    this.$elem = $(selector);
    this.init();
    this.selectMap = {};
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$elem.on('click', '.js-checkbox', (e) => {
      this.toggleItem(e);
      this.changeSelectedNum();
    });

    this.$elem.on('click', '.js-select-all', (e) => {
      let $target = $(e.target);
      if ($target.prop('checked')) {
        this.$elem.find('.js-select-all').prop('checked', true);
        this.addItems();
      } else {
        this.$elem.find('.js-select-all').prop('checked', false);
        this.removeItems();
      }
      this.changeSelectedNum();
    });
  }

  setOpts({ addCb = function() {}, removeCb = function() {} }) {
    this.addCb = addCb;
    this.removeCb = removeCb;
  }

  getItem($elem) {
    return {id: $elem.data('id')};
  }

  removeItem(obj) {
    let $dom = this.$elem.find(`input[data-id="${obj.id}"]`);

    if ($dom.length) {
      $dom.prop('checked', false);
    }

    if (this.selectMap[obj.id]) {
      delete this.selectMap[obj.id];
    }
  }

  addItem(dom) {
    let $dom = $(dom);
    $dom.prop('checked', true);

    if (!this.selectMap[$dom.data('id')]) {
      this.selectMap[$dom.data('id')] = true;
    }
  }

  addItems() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if (!$(item).prop('checked')) {
        this.addItem(item);
        this.addCb && this.addCb(item);
      }
    });
  }

  removeItems() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if ($(item).prop('checked')) {
        let obj = this.getItem($(item));
        this.removeItem(obj);
        this.removeCb && this.removeCb(item);
      }
    });
  }

  toggleItem(e) {
    let $elem = $(e.currentTarget);
    if ($elem.prop('checked')) {
      if (!this.selectMap[$elem.data('id')]) {
        this.selectMap[$elem.data('id')] = true;
        this.addCb && this.addCb($elem);
      }
    } else {
      if (this.selectMap[$elem.data('id')]) {
        delete this.selectMap[$elem.data('id')];
        this.removeCb && this.removeCb($elem);
      }
    }
  }

  resetItems() {
    this.selectMap = {};
  }

  getObjectLength() {
    let arr = Object.keys(this.selectMap);

    return arr.length;
  }

  toJson() {
    return Object.keys(this.selectMap);
  }

  updateTable() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if (this.selectMap[$(item).data('id')]) {
        $(item).prop('checked', true);
      }
    });
    this.changeSelectedNum();
  }

  changeSelectedNum() {
    if (this.$elem.find('.js-select-number').length > 0) {
      this.$elem.find('.js-select-number').text(this.getObjectLength());
    }
  }
}

