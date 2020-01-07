import Selector from '../common/selector';

class TestpaperShow {
  constructor() {
    this.table = $('.js-testpaper-html');
    this.renderUrl = this.table.data('url');
    this.element = $('.js-testpaper-container');
    this.selector = new Selector(this.table);
    this.init();
  }
  init() {
    this.initEvent();
  }
  initEvent() {
    this.element.on('click', '.js-search-btn', (event) => {
      this.onClickSearchBtn(event);
    });

    this.element.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });

    this.element.on('click', '.js-batch-delete', (event) => {
      this.onBatchDelete(event);
    });

    this.element.on('click','.open-testpaper,.close-testpaper', (event) => {
      this.testpaperAction(event);
    });

    this.element.on('click','.js-delete-btn', (event) => {
      this.onDeleteSingle(event);
    });
  }

  onBatchDelete(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let name = $target.data('name');
    let ids = this.selector.toJson();
    if (ids.length === 0) {
      cd.message({type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name': name})});
      return;
    }

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_check_name_hint', {'name': name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), {ids: ids}, function(response) {
        if (response) {
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          self._resetPage();
          self.selector.resetItems();
          self.renderTable();
        } else {
          cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
        }
      }).error(function(error) {
        cd.message({ type: 'danger', message: Translator.trans('site.delete_fail_hint') });
      });
    });
  }

  onDeleteSingle(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let name = $target.data('name');

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name': name}),
      content: Translator.trans('site.data.delete_check_name_hint', {'name': name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), function(html){
        cd.message({type: 'success', message: Translator.trans('testpaper_manage.save_success_hint')});
        self._resetPage();
        self.renderTable();
      }).error(function(){
        cd.message({type: 'danger', message: Translator.trans('testpaper_manage.save_error_hint')});
      });
    });
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

  testpaperAction(event) {
    let $target = $(event.currentTarget);
    let $tr = $target.closest('tr');
    let self = this;

    cd.confirm({
      title: Translator.trans('confirm.oper.tip'),
      content: $target.attr('title'),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($target.data('url'), function(html){
        cd.message({type: 'success', message: Translator.trans('testpaper_manage.save_success_hint')});
        self.renderTable();
      }).error(function(){
        cd.message({type: 'danger', message: Translator.trans('testpaper_manage.save_error_hint')});
      });
    });
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

export default TestpaperShow;

