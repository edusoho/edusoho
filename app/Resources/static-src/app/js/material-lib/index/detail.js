import Info from './info';
import Cover from './cover';

export default class DetailWidget {
  constructor(options) {
    this.callback = options.callback;
    this.element = options.element;
    this.init();
  }
  init() {
    this.initEvent();
    if ($('#cover-tab').length >0) {
      this.cover = new Cover({
        element: $('#cover-tab')
      });
    }

    this.info = new Info({
      element: $('#info-tab')
    });
  }
  initEvent() {
    $('.js-back').on('click', (event) => {
      this.onClickBack(event);
    });
    $('.js-cover').on('click', (event) => {
      this.onClickCover(event);
    });
    $('.js-info').on('click', (event) => {
      this.onClickInfo(event);
    });
  }
  onClickInfo(event) {
    let $target = $(event.currentTarget);
    this._changePane($target);
  }
  onClickCover(event) {
    let $target = $(event.currentTarget);
    this._changePane($target);
  }
  onClickBack() {
    this.back();
  }
  back() {
    this.callback();
    this.element.remove();
    // this.info.destroy();
    // this.cover && this.cover.destroy();
    // this.destroy();
    $('.panel-heading').html(Translator.trans('material_lib.content_title'));
  }
  _changePane($target) {
    //change li
    $target.closest('.nav').find('li.active').removeClass('active');
    $target.addClass('active');

    //change content
    let $tabcontent = $target.closest('.content').find('.tab-content');
    $tabcontent.find('.tab-pane.active').removeClass('active');
    $tabcontent.find($target.data('target')).addClass('active');
  }
}
