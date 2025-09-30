import {shortLongText} from 'app/common/widget/short-long-text';
import Selector from '../common/selector';
import 'store';

class QuestionsShow {
  constructor() {
    this.table = $('.js-question-html');
    this.renderUrl = this.table.data('url');
    this.element = $('.js-question-container');
    this.categoryContainer = $('.js-category-content');
    this.categoryModal = $('.js-category-modal');
    this.selector = new Selector(this.table);
    this.nextBtn = $('.js-next-btn')
    this.modal = $('#modal')
    this.modalUrl = $('[name="introModalUrl"]').val()
    this.accessCloud = $('[name="accessCloud"]').val()
    this.canManageCloud = $('[name="canManageCloud"]').val()
    this.aiAnalysisIntroUrl = $('[name="aiAnalysisIntroUrl"]').val();
    this.init();
  }

  init() {
    this.initEvent();
    this.initSelect();
    this.initShortLongText();
    this.initIntro();
  }

  initEvent() {
    this.element.on('click', '.js-search-btn', (event) => {
      this.onClickSearchBtn(event);
    });

    this.element.on('click', '.js-batch-delete', (event) => {
      this.onDeleteQuestions(event);
    });

    this.element.on('click', '.js-delete-btn', (event) => {
      this.onDeleteSingle(event);
    });

    this.element.on('click', '.js-batch-set-category', (event) => {
      this.showCategoryModal(event);
    });

    this.element.on('click', '.js-batch-set-tag', (event) => {
      this.onSetQuestionsTags(event);
    });

    this.element.on('click', '.js-export-btn', (event) => {
      this.exportQuestions(event);
    });

    this.categoryModal.on('click', '.js-category-btn', (event) => {
      this.setCategory(event);
    });

    this.element.on('click', '.js-update-btn', (event) => {
      this.onUpdateQuestion(event);
    });

    this.element.on('click', '.js-tag-btn', (event) => {
      this.setTags(event);
    });

    $('.js-item-create').click(event => {
      let categoryId = $('#select_category').val();
      let importUrl = $(event.currentTarget).data('url');
      location.href = importUrl + '&categoryId=' + categoryId;
    });

    this.modal.on('click', '.js-next-btn', (event) => {
      this.modal.modal('hide');
      if (this.canManageCloud != 1) {
        this.skipCanManageCloud()
      } else {
        this.skipAccessCloud()
      }
    });

    this.modal.on('click', '.js-close-btn', event => {
      this.modal.modal('hide');
    });
  }

  skipAccessCloud() {
    introJs().setOptions({
      steps: [{
        element: '.js-import-btn',
        intro: Translator.trans('upgrade.cloud.capabilities.to.experience'),
        position: 'left',
      }],
      doneLabel: Translator.trans('skip.upgrade.btn'),
      showBullets: false,
      showStepNumbers: false,
      exitOnEsc: false,
      exitOnOverlayClick: false,
      tooltipClass: 'question-bank-intro-skip',
    }).start()

    $('.introjs-skipbutton').click((event) => {
      const a = document.createElement('a')
      a.target = '_blank'
      a.href = $('.js-skip-btn').attr('href')
      a.click()
    });
  }

  skipCanManageCloud() {
    introJs().setOptions({
      steps: [{
        element: '.js-import-btn',
        intro: Translator.trans('next.skip.intro.text'),
        position: 'left'
      }],
      doneLabel: Translator.trans('skip.i.know'),
      showBullets: false,
      showStepNumbers: false,
      exitOnEsc: false,
      exitOnOverlayClick: false,
      tooltipClass: 'question-bank-intro-skip',
    }).start();
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

  initIntro() {
    if (!store.get('QUESTION_IMPORT_INTRO') && this.accessCloud != 1) {
      store.set('QUESTION_IMPORT_INTRO', true);
      this.modal.load(this.modalUrl);
      this.modal.modal('show');
    }

    if (!store.get('QUESTION_AI_ANALYSIS_INTRO')) {
      store.set('QUESTION_AI_ANALYSIS_INTRO', true);
      this.modal.load(this.aiAnalysisIntroUrl);
      this.modal.modal('show');
    }
  }

  onUpdateQuestion(event) {
    let $target = $(event.currentTarget);
    let updateUrl = $target.data('url');

    if (updateUrl.indexOf('/questions/show/ajax') !== -1) {
      updateUrl = updateUrl.replace('/questions/show/ajax', '/questions');
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
    $.post(url, data, function (response) {
      if (response) {
        cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
        self.selector.resetItems();
        self.renderTable(true);
        self.categoryModal.modal('hide');
      } else {
        cd.message({type: 'danger', message: Translator.trans('site.save_error_hint')});
      }
    }).error(function (error) {
      cd.message({type: 'danger', message: Translator.trans('site.save_error_hint')});
    });
  }

  setTags(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let id = $target.data('id');
    window.emitter.emit('open-tag-modal', {id: id, mode: 'set'})
    window.emitter.on('set-tag-success', () => {
      self.selector.resetItems();
      self.renderTable(true);
    })
  }

  onSetQuestionsTags(event) {
    let self = this;
    let ids = this.selector.toJson();
    window.emitter.emit('open-tag-modal', {ids: ids, mode: 'set'})
    window.emitter.on('set-tag-success', () => {
      self.selector.resetItems();
      self.renderTable(true);
    })
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
      $.post($target.data('url'), {ids: ids}, function (response) {
        if (response) {
          cd.message({type: 'success', message: Translator.trans('site.delete_success_hint')});
          self.selector.resetItems();
          self.renderTable(true);
        } else {
          cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
        }
      }).error(function (error) {
        cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
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
          cd.message({type: 'success', message: Translator.trans('site.delete_success_hint')});
          self.selector.resetItems();
          self.renderTable(true);
        } else {
          cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
        }
      }).error(function (error) {
        cd.message({type: 'danger', message: Translator.trans('site.delete_fail_hint')});
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

  onChangePagination() {
    let self = this;
    const currentPerpage = $('.js-current-perpage-count').children('option:selected').val()
    const serialize = this.element.find('[data-role="search-conditions"]').serialize()
    const conditions = `${serialize}&page=1&perpage=${currentPerpage}`;
    this._loading();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: conditions
    }).done(function (resp) {
      self.table.html(resp);
      self.selector.updateTable();
    }).fail(function () {
      self._loaded_error();
    });
  }

  onClickCategorySearch(event) {
    let $target = $(event.currentTarget);
    this.categoryContainer.find('.js-active-set.active').removeClass('active');
    $target.addClass('active');
    $('.js-category-choose').val($target.data('id'));
    const defaultPages = 10
    this.renderTable('', defaultPages);
  }

  onClickAllCategorySearch(event) {
    let $target = $(event.currentTarget);
    this.categoryContainer.find('.js-active-set.active').removeClass('active');
    $target.addClass('active');
    $('.js-category-choose').val('');
    const defaultPages = 10
    this.renderTable('', defaultPages);
  }

  renderTable(isPaginator, defaultPages) {
    isPaginator || this._resetPage();
    let self = this;
    const perpage = defaultPages ? defaultPages : $('.js-current-perpage-count').children('option:selected').val()
    const page = this.element.find('.js-page').val()
    const category_id = $('.js-category-choose').val()
    const difficulty = $('.js-list-header-difficulty').val() === 'default' ? '' : $('.js-list-header-difficulty').val()
    const type = $('.js-list-header-type').val() === 'default' ? '' : $('.js-list-header-type').val()
    const keyword = $('.js-list-header-keyword').val() === 'default' ? '' : $('.js-list-header-keyword').val()
    const tagIds = $('.js-list-header-tagIds').val() ? $('.js-list-header-tagIds').val().split(',') : null

    const data = {
      category_id,
      difficulty,
      type,
      keyword,
      tagIds,
      perpage,
      page
    };

    this._loading();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data
    }).done(function (resp) {
      self.table.html(resp);
      self.selector.updateTable();
    }).fail(function () {
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

