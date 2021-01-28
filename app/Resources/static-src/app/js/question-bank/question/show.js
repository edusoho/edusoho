import {shortLongText} from 'app/common/widget/short-long-text';
import Selector from '../common/selector';

class QuestionsShow {
  constructor() {
    this.table = $('.js-question-html');
    this.renderUrl = this.table.data('url');
    this.element = $('.js-question-container');
    this.categoryContainer = $('.js-category-content');
    this.categoryModal = $('.js-category-modal');
    this.selector = new Selector(this.table);
    this.init();
  }
  init() {
    this.initEvent();
    this.initSelect();
    this.initShortLongText();
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

    this.element.on('click', '.js-batch-delete', (event) => {
      this.onDeleteQuestions(event);
    });

    this.element.on('click','.js-delete-btn', (event) => {
      this.onDeleteSingle(event);
    });

    this.element.on('click', '.js-batch-set', (event) => {
      this.showCategoryModal(event);
    });

    this.element.on('click', '.js-export-btn', (event) => {
      this.exportQuestions(event);
    });

    this.categoryModal.on('click', '.js-category-btn', (event) => {
      this.setCategory(event);
    });

    this.element.on('click','.js-update-btn', (event) => {
      this.onUpdateQuestion(event);
    });
  }

  initSelect() {
    $('#question_categoryId').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first'
    });
  }

  initShortLongText() {
    shortLongText($('#quiz-table-container'));
  }

  onUpdateQuestion(event) {
    let $target = $(event.currentTarget);
    let updateUrl = $target.data('url');

    if(updateUrl.indexOf("/questions/show/ajax") !== -1){
      updateUrl = updateUrl.replace('/questions/show/ajax','/questions');

    }
    window.location.href = updateUrl;
  }

  showCategoryModal(event) {
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }
    this.categoryModal.modal('show');
  }

  exportQuestions(event) {
    let $target = $(event.currentTarget);
    let conditions = this.element.find('[data-role="search-conditions"]').serialize();
    let url = $target.data('url');
    let ids = this.selector.toJson();
    $target.attr('href', url + '?' + conditions + '&ids=' + ids);
  }

  setCategory(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let url = $target.data('url');
    let data = {
      ids: this.selector.toJson(),
      categoryId: $('#question_categoryId').val()
    };
    $.post(url, data, function(response) {
      if (response) {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
        self.selector.resetItems();
        self.renderTable(true);
        self.categoryModal.modal('hide');
      } else {
        cd.message({ type: 'danger', message: Translator.trans('site.save_error_hint') });
      }
    }).error(function(error) {
      cd.message({ type: 'danger', message: Translator.trans('site.save_error_hint') });
    });
  }

  onDeleteQuestions(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    let content = '<br><div class="help-block">' + Translator.trans('course.question_manage.manage.delete_tips') + '</div>';
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_check_name_hint', {'name': name}) + content,
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), {ids: ids}, function(response) {
        if (response) {
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          self.selector.resetItems();
          self.renderTable(true);
        } else {
          cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
        }
      }).error(function(error) {
        cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
      });
    });
  }

  onDeleteSingle(event) {
    let $btn = $(event.currentTarget);

    let name = $btn.data('name');
    let self = this;
    let content = '<br><div class="help-block">' + Translator.trans('course.question_manage.manage.delete_tips') + '</div>';

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_name_hint', {'name': name}) + content,
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($btn.data('url'), function (response) {
        if (response) {
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          self.selector.resetItems();
          self.renderTable(true);
        } else {
          cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
        }
      }).error(function(error) {
        cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
      });
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

  renderTable(isPaginator) {
    isPaginator || this._resetPage();
    let self = this;
    let conditions = this.element.find('[data-role="search-conditions"]').serialize() + '&page=' + this.element.find('.js-page').val();
    this._loading();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: conditions
    }).done(function(resp){
      self.table.html(resp);
      self.selector.updateTable();
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

export default QuestionsShow;

