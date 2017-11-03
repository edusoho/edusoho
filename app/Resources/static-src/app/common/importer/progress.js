class Progress {
  constructor(props) {
    Object.assign(this, {
      importData: [],
      $container: null,
      quantity: 0,
      total: 0
    }, props);
    
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
    this.total = this.importData.length;
    let progress = parseInt(this.quantity / this.total * 100) + '%';

    this.$el.find('.progress-bar-success').css('width', progress);
    this.$el.find('.progress-text').text('已经导入: ' + this.quantity + '条数据');
    this.$el.find('.js-import-progress-text').removeClass('hidden');
  }

  onComplate() {
     this.$container.find('.progress-bar').css('width', '100%');
    //  this.$container.find('a').removeClass('hidden');
     this.$container.find('.progress-text').text('导入成功, 总共导入: ' + this.quantity + '条数据');
     this.$container.find('.js-import-progress-text').addClass('hidden');
  }

  import(index) {
    if (!this.importData[index]) {
      this.onComplate();
      return;
    }

    const data = {};
    data.importData = this.importData[index];
    $.post(this.$container.data('importUrl'), data).done((res) => {
      this.quantity = this.quantity + 1;
      this.onProgress();
      this.import(index + 1);
    }).fail((res) => {
      this.onError();
    })
  }
}

export default Progress;