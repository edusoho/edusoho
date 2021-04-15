export default class ItemConfirm {

  constructor(prop) {
    this.element = $(prop.element);
    this.dataRole = prop.dataRole;
    this.itemConfirm();
  }

  itemConfirm() {
    this.element.on('click', '[data-role=' + this.dataRole + ']', function() {
      let $btn = $(this),
        name = $btn.data('name'),
        message = $btn.data('message');

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

    });
  }
}