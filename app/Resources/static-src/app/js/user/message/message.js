import notify from 'common/notify';

class Message {
  constructor(options) {
    this.$element = $(options.element);
    this.validator();
  }

  validator() {
    let $element = this.$element;
    $element.validate({
      rules: {
        'message[receiver]': {
          required: true,
          es_remote: true,
          chinese_alphanumeric: true
        },
        'message[content]': {
          required: true,
          maxlength: 500
        }
      },
      ajax: true,
      submitSuccess() {
        notify('success', Translator.trans('notify.message_send_success'));
        $element.closest('.modal').modal('hide');
      },
      submitError(response) {
        notify('danger', Translator.trans(response.responseJSON.error.message));
      }
    });
  }
}

export default Message;