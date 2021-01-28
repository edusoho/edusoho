export default class ShowUnpublish {
  constructor(element) {
    this.$element = $(element);
    this._event();
  }

  _event() {
    cd.onoff({
      el: '.js-switch'
    }).on('change', (value) => {
      const url = this.$element.data('url');
      const status = this.$element.parent().hasClass('checked') ? 1 : 0;
      $.post(url, { status: status })
      .success((response) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
      })
      .error((response) => {
        cd.message({ type: 'danger', message: response.error.message });
      })
    })
  }
}
