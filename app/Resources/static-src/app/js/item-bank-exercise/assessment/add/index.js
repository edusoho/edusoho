import Selector from "../../../question-bank/common/selector";

class BatchAddAssessmentExercise {
  constructor() {
    this.table = $('.js-testpaper-html');
    this.selector = new Selector(this.table);
    this.renderUrl = $('#renderUrl').data('url');
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.table.on('click', '#batch-add', (event) => {
      this.onBatchAdd(event);
    });

    this.table.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });
  }

  onBatchAdd(event) {
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }

    cd.confirm({
      title: Translator.trans('site.data.add_title_hint', {'name': name}),
      content: Translator.trans('site.data.add_check_name_hint', {'name': name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), {ids: ids}, function (response) {
        if (response) {
          cd.message({type: 'success', message: Translator.trans('site.add_success_hint')});
          window.location.reload();
        } else {
          cd.message({type: 'danger', message: Translator.trans('site.add_fail_hint')});
        }
      }).error(function (error) {
        cd.message({type: 'danger', message: Translator.trans('site.add_fail_hint')});
      });
    });
  }

  onClickPagination(event) {
    let $target = $(event.currentTarget);
    this.table.find('.js-page').val($target.data('page'));
    this.renderTable(true);
    event.preventDefault();
  }

  renderTable(isPaginator) {
    isPaginator || this._resetPage();
    let self = this;
    let conditions = this.table.find('[data-role="search-conditions"]').serialize() + '&page=' + this.table.find('.js-page').val();
    this._loading();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: conditions
    }).done(function(resp){
      self.table.html(resp);
      self.table.updateTable();
    }).fail(function(){
      self._loaded_error();
    });
  }

  _resetPage() {
    this.table.find('.js-page').val(1);
  }

  _loading() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading') + '</div>';
    this.table.html(loading);
  }

  _loaded_error() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
    this.table.html(loading);
  }
}

new BatchAddAssessmentExercise();


