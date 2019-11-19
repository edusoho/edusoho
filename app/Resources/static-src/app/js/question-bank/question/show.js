import BatchSelect from 'app/common/widget/res-batch-select';
import { toggleIcon } from 'app/common/widget/chapter-animate';

class QuestionsShow {
  constructor() {
    this.model = 'normal';
    this.renderUrl = $('.js-question-html').data('url');
    this.attribute = 'mine';
    this.element = $('.js-question-container');
    this.categoryContainer = $('.js-category-content');
    this.init();
  }
  init() {
    this.initEvent();
    this.initCategoryShow();
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

  initCategoryShow() {
    $('.js-toggle-show').on('click', (event) => {
      let $this = $(event.target);
      let $sort = $this.closest('.js-sortable-item');
      $sort.nextUntil('.js-sortable-item').animate({
        height: 'toggle',
        opacity: 'toggle'
      }, "normal");
    
      toggleIcon($sort, 'cd-icon-add', 'cd-icon-remove');
    });
    
    const $currentItem = $('.js-active-item');
    const $currentParents = $currentItem.parents('.js-sortable-list');
    const $showIcon = $currentParents.prev('.js-sortable-item').find('i');
    $currentParents.show();
    $showIcon.removeClass('cd-icon-add').addClass('cd-icon-remove');
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
    this.categoryContainer.find('.js-active-set.active').removeClass('active');
    console.log(this.element.find('.js-active-set.active'));
    $target.addClass('active');
    $('.js-category-choose').val($target.data('id'));
    this.renderTable();
  }

  onClickAllCategorySearch(event) {
    let $target = $(event.currentTarget);
    this.categoryContainer.find('.js-active-set.active').removeClass('active');
    $target.addClass('active');
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

