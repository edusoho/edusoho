export default class ItemDelete {

  constructor(prop) {
    this.element = $(prop.element);
    this.itemDelete();
  }

  itemDelete() {
    this.element.on('click', '[data-role=item-delete]', function(onSuccess) {
      let $btn = $(this),
        name = $btn.data('name'),
        message = $btn.data('message');

      if (!message) {
        message = Translator.trans('admin.util.item_delete.delete_hint',{name:name});
      }

      if (!confirm(message)) {
        return ;
      }

      $.post($btn.data('url'), function() {
        if ($.isFunction(onSuccess)) {
          onSuccess.call($element, $item);
        } else {
          $btn.parents('[data-role=item]').remove();
          cd.message({ type: 'success', message: Translator.trans('admin.util.item_delete.delete_success_hint',{name:name}) });
        }
      });

    });
  }
}