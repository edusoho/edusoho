import BatchSelect from 'app/common/widget/res-batch-select';

class QuestionsShow {
  constructor() {
    this.model = 'normal';
    this.renderUrl = $('.js-question-html').data('url');
    this.attribute = 'mine';
    this.element = $('.js-question-container');
    this.init();
  }
  init() {
    this.initEvent();
    new BatchSelect(this.element);
  }
  initEvent() {
    this.element.on('click', '.js-search-btn', (event) => {
      this.onClickSearchBtn(event);
    });

    this.element.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });

    this.element.on('click', '.js-category-search', (event) => {
      this.onClickCategorySearch(event);
    });

    this.element.on('click', '.js-all-category-search', (event) => {
      this.onClickAllCategorySearch(event);
    });
  }

  // 搜索
  onClickSearchBtn(event) {
    this.renderTable();
    event.preventDefault();
  }

  onClickPagination(event) {
    let $target = $(event.currentTarget);
    this.element.find('.js-page').val($target.data('page'));
    this.renderTable(true);
    event.preventDefault();
  }

  onClickCategorySearch(event) {
    let $target = $(event.currentTarget);
    $('.js-category-choose').val($target.data('id'));
    this.renderTable();
  }

  onClickAllCategorySearch(event) {
    $('.js-category-choose').val('');
    this.renderTable();
  }

  renderTable(isPaginator) {
    isPaginator || this._resetPage();
    let self = this;
    let $table = $('.js-question-html');
    this._loading();
    var conditions = this.element.find('[data-role="search-conditions"]').serialize() + '&page=' + this.element.find('.js-page').val();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: conditions
    }).done(function(resp){
      $table.html(resp);
      // $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', true);
      // $('[data-toggle="tooltip"]').tooltip();
      // let mode = self.model;
      // let attribute = self.attribute;
      // if (mode == 'edit' && attribute == 'mine') {
      //   $('#material-lib-batch-bar').show();
      //   $('#material-lib-items-panel').find('[data-role=batch-item]').show();
      //   $('[data-role=batch-select]').attr('checked',false);
      // } else if (mode == 'normal') {
      //   $('#material-lib-batch-bar').hide();
      // }
      // let $temp = $table.find('.js-paginator');
      // self.element.find('[data-role=paginator]').html($temp.html());
      // $('.js-table-popover').popover({
      //   placement: 'top',
      //   trigger: 'manual',
      //   html: true,
      //   animation: false,
      //   title: `<div class="clearfix material-table-popover">${Translator.trans('material.common_table.transcoding_intro')}<a class="pull-right cd-text-sm" href="http://www.qiqiuyu.com/faq/868/detail" target="_blank">${Translator.trans('material.common_table.transcoding_more')}</a></div>`,
      //   content: `
      //   <div class="cd-text-sm">
      //     <p class="mb0"><strong>${Translator.trans('subtitle.status.error')}：</strong>${Translator.trans('material.common_table.fail_error_tip')}</p>
      //     <p class="mb0"><strong>${Translator.trans('material.common_table.fail_not_support')}：</strong>${Translator.trans('material.common_table.not_support_error_tip')}</p>
      //   </div>`
      // }).on('mouseenter', function () {
      //   const _this = this;
      //   $(this).popover('show');
      //   $('.popover').on('mouseleave', function () {
      //     $(_this).popover('hide');
      //   });
      // }).on('mouseleave', function () {
      //   const _this = this;
      //   setTimeout(function () {
      //     if (!$('.popover:hover').length) {
      //       $(_this).popover("hide");
      //     }
      //   }, 300);
      // });
    }).fail(function(){
      self._loaded_error();
    });
  }
  _loading() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading') + '</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _loaded_error() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _resetPage() {
    this.element.find('.js-page').val(1);
  }
}

export default QuestionsShow;

