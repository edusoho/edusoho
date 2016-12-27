import notify from 'common/notify';

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

    if (!message) {
      message = '真的要删除该'+name+'吗？';
    }

    if (!confirm(message)) {
      return ;
    }

    $.post($btn.data('url'), function() {
      if ($.isFunction(self.onSuccess)) {
        self.onSuccess.call(self.$element);
      } else {
        $btn.closest('[data-role=item]').remove();
        notify('success', "删除成功");
        window.location.reload();
      }
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
      notify('danger', '未选中任何'+name);
      return ;
    }

    if (!confirm('确定要删除选中的条'+name+'吗？')) {
        return ;
    }

    notify('info', '正在删除...');

    $.post($btn.data('url'), {ids:ids}, function(){
      window.location.reload();
    });
  }
}

export default DeleteAction;