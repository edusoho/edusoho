class Progress {
  constructor(props) {
    Object.assign(this, {
      importData: [],
      $container: null,
      quantity: 0,
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
        this.chunkData[i].push(value)
      } else {
        this.chunkData[i] = [];
        this.chunkData[i][0] = value;
      }
    });

    this.init();
  }

  init() {
    console.log('import');
    this.import(0);
  }

  onError() {
    this.$container.find('.progress-bar').css('width', '100%')
    .removeClass('progress-bar-success')
    .addClass('progress-bar-danger');

    this.$container.find('.progress-text').text('发生未知错误').removeClass('text-success').addClass('text-danger');
    // this.$container.find('a').removeClass('hidden').text('重新导入');
  }

  onProgress() {
    let progress = parseInt(this.quantity / this.total * 100) + '%';

    this.$container.find('.progress-bar-success').css('width', progress);
    this.$container.find('.progress-text').text('已经导入: ' + this.quantity + '条数据');
    this.$container.find('.js-import-progress-text').removeClass('hidden');
  }

  onComplate() {
     this.$container.find('.progress-bar').css('width', '100%');
    //  this.$container.find('a').removeClass('hidden');
     this.$container.find('.progress-text').text('导入成功, 总共导入: ' + this.quantity + '条数据');
     this.$container.find('.js-import-progress-text').addClass('hidden');
  }

  import(index) {
    if (!this.chunkData[index]) {
      this.onComplate();
      return;
    }

    this.data.importData = this.chunkData[index];
    $.post(this.$container.data('importUrl'), this.data).then((res) => {
      this.quantity = this.quantity + this.chunkData[index].length;
      this.onProgress();
      this.import(index + 1);
    }, (res) => {
      this.onError();
    })
  }
}

export default Progress;