class Progress {
  constructor(props) {
    Object.assign(this, {
      importData: [],
      $container: null,
      quantity: 0,
      successCount:0,
      total: 0,
      checkType: 'ignore',
      chunkSize: 8,
      chunkData: []
    }, props);

    this.data = Object.assign({
      checkType: this.checkType,
      type: this.$container.data('type'),
      importData: []
    }, this.formData);

    this.total = this.importData.length;

    this.importData.forEach((value, index) => {
      let i = Math.floor(index / this.chunkSize);
      if (this.chunkData[i]) {
        this.chunkData[i].push(value);
      } else {
        this.chunkData[i] = [];
        this.chunkData[i][0] = value;
      }
    });

    this.init();
  }

  init() {
    this.import(0);
    this.events();
  }

  events() {
    this.$container.on('click', '.js-import-finish-btn', event => this.onFinish(event));
  }

  onFinish(event) {
    let $this = $(event.currentTarget);
    $this.button('loading');
    window.location.reload();
  }

  onError() {
    this.$container.find('.progress-bar').css('width', '100%')
      .removeClass('progress-bar-success')
      .addClass('progress-bar-danger');

    this.$container.find('.progress-text').text(Translator.trans('importer.import_error')).removeClass('text-success').addClass('text-danger');
    this.$container.find('.js-import-finish-btn').removeClass('hidden').text(Translator.trans('importer.import_reselect_btn'));
  }

  onProgress() {
    let progress = parseInt(this.quantity / this.total * 100) + '%';

    this.$container.find('.progress-bar').css('width', progress);
    this.$container.find('.progress-text').text(Translator.trans('importer.import_progress_data', {'number': this.quantity}));
    this.$container.find('.js-import-progress-text').removeClass('hidden');
  }

  onComplate() {
    this.$container.find('.progress-bar').css('width', '100%');
    this.$container.find('.progress-text').text(Translator.trans('importer.import_finish_data', {'number': this.successCount}));
    this.$container.find('.js-import-progress-text').addClass('hidden');
    this.$container.find('.js-import-finish-btn').removeClass('hidden');
  }

  import(index) {
    if (!this.chunkData[index]) {
      this.onComplate();
      return;
    }

    this.data.importData = this.chunkData[index];
    $.post(this.$container.data('importUrl'), this.data).then((res) => {
      if(res.successCount){
          this.successCount = this.successCount + res.successCount;
      }
      this.quantity = this.quantity + this.chunkData[index].length;
      this.onProgress();
      this.import(index + 1);
    }, (res) => {
      this.onError();
    });
  }
}

export default Progress;