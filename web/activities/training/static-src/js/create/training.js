export default class Training {
  constructor($iframeContent) {
    this.$trainingModal = $('#modal', window.parent.document);
    this.$imagesPickedModal = $('#attachment-modal', window.parent.document);
    this.$element = $iframeContent;
    this.$step2_form = this.$element.find('#step2-form');
    this.$step3_form = this.$element.find('#step3-form');
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click', '[data-role="pick-item"]', event => this.showPickImages(event));
    this.$imagesPickedModal.on('shown.bs.modal', () => {
      this.$trainingModal.hide();
    });
    this.$imagesPickedModal.on('hidden.bs.modal', () => {
      this.showPickedImages();
      this.$trainingModal.show();
      this.$imagesPickedModal.html('');
    });
  }

  showPickImages(event) {
    event.preventDefault();
    let $btn = $(event.currentTarget);
    this.$imagesPickedModal.modal().data('manager', this);
    // 需要传递一个选中参数过去，来判断之前是否选中
    $.get($btn.data('url'), {}, html => {
      this.$imagesPickedModal.html(html);
    });
  }

  showPickedImages() {
    let $cachedImages = $('.js-cached-question');
    if ($cachedImages.text() === '') {
      return;
    }
    alert($cachedImages.text())
    
  }
}
