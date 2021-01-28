class DeleteAction {
  constructor($element,onSuccess) {
    this.$element = $element;
    this.onSuccess = onSuccess;
    this.initEvent();
  }

  initEvent() {
    this.$element.on('click','[data-role="item-delete"]', event=>this._itemDelete(event));
    this.$element.on('click','[data-role="batch-delete"]', event=>this._batchDelete(event));
  }

  _itemDelete(event) {
    let $btn = $(event.currentTarget);

    let name = $btn.data('name');
    let message = $btn.data('message');
    let self = this;

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name':name}),
      content: Translator.trans('site.data.delete_name_hint', {'name':name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($btn.data('url'), function() {
        if ($.isFunction(self.onSuccess)) {
          self.onSuccess.call(self.$element);
        } else {
          $btn.closest('[data-role=item]').remove();
          cd.message({ type: 'success', message: Translator.trans('site.delete_success_hint') });
          window.location.reload();
        }
      });
    });
  }

  _batchDelete(event) {
    let $btn = $(event.currentTarget);
    let name = $btn.data('name');

    let ids = [];
    this.$element.find('[data-role="batch-item"]:checked').each(function(){
      ids.push(this.value);
    });

    if (ids.length == 0) {
      cd.message({ type: 'danger', message: Translator.trans('site.data.uncheck_name_hint', {'name':name}) });
      return ;
    }

    cd.confirm({
      title: Translator.trans('site.data.delete_title_hint', {'name':name}),
      content: Translator.trans('site.data.delete_check_name_hint', {'name':name}),
      okText: Translator.trans('site.confirm'),
      cancelText: Translator.trans('site.close'),
    }).on('ok', () => {
      $.post($btn.data('url'), {ids:ids}, function() {
        window.location.reload();
      });
    });
  }
}

export default DeleteAction;