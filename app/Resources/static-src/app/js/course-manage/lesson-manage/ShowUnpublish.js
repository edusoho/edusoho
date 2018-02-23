import notify from "common/notify";

export default class ShowUnpublish {
  constructor(element) {
    this.$element = $(element);
    this._event();
  }

  _event() {
    this.$element.on('change', function(){
      const url = $(this).data('url');
      const status = $(this).parent('.js-switch').hasClass('checked') ? 0 : 1;
      $.post(url, {status:status})
      .success(function(response) {
        notify('success', Translator.trans('site.save_success_hint'));
      })
      .error(function(response){
        notify('error', response.error.message);
      })
    })
  }
}
