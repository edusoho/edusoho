import notify from 'common/notify';
import Select from 'app/common/input-select';

export default class Info {
  constructor(options) {
    this.element = options.element;
    this.callback = options.callback;
    this.init();
  }
  init() {
    this.initEvent();
    this._initTag();
  }
  initEvent() {
    $('#info-form').on('submit', (event) => {
      this.onSubmitInfoForm(event);
    });
  }
  _initTag() {
    Select('#infoTags', 'remote', {
      width: 'off'
    });
  }
  onSubmitInfoForm(event) {
    let $target = $(event.currentTarget);
    $target.find('#info-save-btn').button('loading');
    $.ajax({
      type: 'POST',
      url: $target.attr('action'),
      data: $('#info-form').serialize()

    }).done(function() {
      notify('success', Translator.trans('site.save_success_hint'));

    }).fail(function() {
      notify('danger', Translator.trans('site.save_error_hint'));

    }).always(function() {
      $target.find('#info-save-btn').button('reset');
    });

    event.preventDefault();
  }
}
