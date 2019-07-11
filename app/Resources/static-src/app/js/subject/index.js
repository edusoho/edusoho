import notify from 'common/notify';

export default class sbList {
  constructor() {
    this.$element = $('.js-subject-list');
    this.$batchBtn = $('.js-batch-btn');
    this.$batchWrap = $('.js-subject-wrap');
    this.$sbCheckbox = $('.js-show-checkbox');
    this.$finishBtn = $('.js-finish-btn');
    this.$allBtn = $('.js-batch-select');
    this.$anchor = $('.js-subject-anchor');
    this.flag = true;
    this.$modal = $('#question-difficulty-modal');
    this.init();
  }

  init() {
    this.confirmFresh();
    this.sbListFixed();
    this.initEvent();
  }

  confirmFresh() {
    // $(window).on('beforeunload',function(){
    //   return Translator.trans('admin.block.not_saved_data_hint');
    // });
  }

  initEvent() {
    this.$element.on('click','.js-batch-select', event => this.batchSelect(event));
    this.$element.on('click','.js-batch-btn', event =>this.batchBtnClick(event));
    this.$element.on('click','.js-finish-btn',event => this.finishBtnClick(event));
    this.$element.on('click','*[data-anchor]',event => this.quickToQuestion(event, this.flag));
    this.$element.on('click','.js-finish-btn', event => this.finishBtnClick(event));
    this.$element.on('click','.js-difficult-setting', event => this.showModal(event, this.$modal));
  }

  sbListFixed() {
    let width = $('.js-subject-data').width();
    if (!this.$element.length) {
      return;
    }
    const self = this;
    let listTop = this.$element.offset().top;
    $(window).scroll(function(event) {
      self.$element.width(width);
      if ($(window).scrollTop() >= listTop) {
        self.$element.addClass('sb-fixed');
      } else {
        self.$element.removeClass('sb-fixed');
      }
    });
  }

  batchSelect(event) {
    if (event.currentTarget !== event.target) {
      return;
    }
    this.$sbCheckbox.each(function() {
      const $this = $(this);
      $this[0].click();
    });
  }

  batchBtnClick(event) {
    const $target = $(event.target);
    $target.toggleClass('hidden');
    this.toggleClass();
    this.$anchor.addClass('sb-cursor-default');
    this.flag = false;
  }

  finishBtnClick(event) {
    this.$batchBtn.toggleClass('hidden');
    this.toggleClass();
    this.$anchor.removeClass('sb-cursor-default');
    this.flag = true;
  }

  toggleClass() {
    this.$batchWrap.toggleClass('hidden');
    this.$sbCheckbox.toggleClass('hidden');
  }

  quickToQuestion(event, flag) {
    if (!flag) {
      return;
    }
    const $target = $(event.currentTarget);
    const position = $($target.data('anchor')).offset();
    $(document).scrollTop(position.top);
  }

  showModal(event, modal) {
    let stats = this.statChosedQuestion();
    var keys = Object.keys(stats);
    if (keys.length == 0) {
      notify('danger', Translator.trans('请选择题目'));
      return;
    }
    let html = '';
    $.each(stats, function(index, statsItem){
      let tr = statsItem.count + statsItem.name + '，';
      html += tr;
    });
    html = html.substring(0, html.length - 1) + '。';

    modal.find('.js-select').html(html);

    modal.modal('show');
  }

  statChosedQuestion() {
    let stats = {};
    let self = this;

    self.$element.find('.js-show-checkbox.checked').each(function(){
      let type = $(this).data('type'),
        name = $(this).data('name');

      if (typeof stats[type] == 'undefined') {
        stats[type] = {name:name, count:1};
      } else {
        stats[type]['count']++;
      }
    });

    return stats;
  }
}

new sbList();
