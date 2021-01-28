import Selector from '../common/selector';
import { htmlEscape } from 'app/common/unit.js';

class QuestionSelect {
  constructor() {
    this.element = $('.js-select-container');
    this.table = $('.js-select-table');
    this.$questionBankSelector = $('.js-question-bank');
    this.renderUrl = this.table.data('url');
    this.categoryContainer = $('.js-category-list');
    this.selectTypeQuestion = {};
    this.selector = new Selector(this.table);
    this.init();
  }
  init() {
    this.initEvent();
    this.initQuestionBankSelector();
    this.initQuestionType();
    this.selector.setOpts({
      addCb: (item) => {
        this.selectQuestion(item);
      },
      removeCb: (item) => {
        this.removeQuestion(item);
      }
    });
    this.initToolTip();
  }
  initEvent() {
    this.element.on('change', '.js-question-bank', (event) => {
      this.onChangeQuestionBank(event);
    });

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

    this.element.on('click', '.js-clear-select', (event) => {
      this.onClickClearSelect(event);
    });

    $('.js-pick-btn').on('click', (event) => {
      this.onClickPick(event);
    });
  }

  initToolTip() {
    $('a[data-toggle=tooltip]').tooltip({container: 'body'});
  }

  initQuestionBankSelector() {
    if (this.$questionBankSelector.length !== 0) {
      this.$questionBankSelector.select2({
        treeview: true,
        dropdownAutoWidth: true,
        treeviewInitState: 'collapsed',
        placeholderOption: 'first',
        formatResult: function(state) {
          let text = htmlEscape(state.text);
          if (!state.id) {
            return text;
          }
          return `<div class="select2-result-text"><span class="select2-match"></span><span><i class="es-icon es-icon-tiku"></i>${text}</span></div>`;
        },
        dropdownCss: {
          width: ''
        },
      });
    }
  }

  initQuestionType() {
    this.element.find('.js-list-item').each((index, item) => {
      let type = $(item).data('type');
      this.selectTypeQuestion[type] = {};
    });
  }

  selectQuestion(item) {
    this.element.find('.js-select-number').text(this.selector.getObjectLength());
    let type = $(item).data('type');
    let id = $(item).data('id');
    if (this.selectTypeQuestion[type]) {
      this.selectTypeQuestion[type][id] = true;
      this.element.find('.js-select-' + type).text(this.getTypeQuestionLength(type));
    }
  }

  removeQuestion(item) {
    this.element.find('.js-select-number').text(this.selector.getObjectLength());
    let type = $(item).data('type');
    let id = $(item).data('id');
    if (this.selectTypeQuestion[type][id]) {
      delete this.selectTypeQuestion[type][id];
      this.element.find('.js-select-' + type).text(this.getTypeQuestionLength(type));
    }
  }

  onChangeQuestionBank(event) {
    let $selected = this.$questionBankSelector.select2('data');
    let bankId = $selected.id;
    if (parseInt(bankId)) {
      let url = this.$questionBankSelector.data('url');
      url = url.replace(/[0-9]/, bankId);
      $.post(url, {isSelectBank:1}, function(html) {
        $('#attachment-modal').html(html);
      }).error(function(e) {
        cd.message({type: 'danger', message: e.responseJson.error.message});
      })
    }
  }

  onClickClearSelect(event) {
    this.element.find('.js-list-item').each((index, item) => {
      let type = $(item).data('type');
      this.selectTypeQuestion[type] = {};
      this.element.find('.js-select-' + type).text(0);
    });
    this.selector.removeItems();
    this.selector.resetItems();
    this.element.find('.js-select-number').text(0);
  }

  onClickPick(event) {
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }
    this.cacheQuestionAndBank();

    let $modal = this.element.parents('.modal');
    $modal.trigger('selectQuestion', this.selectTypeQuestion);
    $modal.modal('hide');
    $('.js-close-modal').trigger('click');
  }

  getTypeQuestionLength(type) {
    if (this.selectTypeQuestion[type]) {
      let arr = Object.keys(this.selectTypeQuestion[type]);
      return arr.length;
    }

    return 0;
  }

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

  cacheQuestionAndBank() {
    let $content = $('#task-create-content-iframe').contents();
    $content.find('.js-cached-question').text(JSON.stringify(this.selectTypeQuestion));
    let bankId = this.$questionBankSelector.select2('data').id,
      $originBank = $content.find('.js-origin-bank'),
      $currentBank = $content.find('.js-current-bank');
    if ($.trim($currentBank.val()) === '') {
      $originBank.val(bankId);
    } else {
      $originBank.val($currentBank.val());
    }
    $currentBank.val(bankId);
  }

  renderTable(isPaginator) {
    isPaginator || this._resetPage();
    let conditions = this.element.find('[data-role="search-conditions"]').serialize() + '&page=' + this.element.find('.js-page').val();
    conditions += '&exclude_ids=' + $('.js-excludeIds').val();
    this._loading();
    let self = this;
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: conditions
    }).done(function(resp){
      self.table.html(resp);
      self.selector.updateTable();
      self.initToolTip();
    }).fail(function(){
      self._loaded_error();
    });
  }
  _loading() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading') + '</div>';
    this.table.html(loading);
  }
  _loaded_error() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
    this.table.html(loading);
  }
  _resetPage() {
    this.element.find('.js-page').val(1);
  }
}

new QuestionSelect();

