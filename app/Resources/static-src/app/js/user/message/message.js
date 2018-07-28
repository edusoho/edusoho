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
        cd.message({ type: 'success', message: Translator.trans('private_message.send_success') });
        $element.closest('.modal').modal('hide');
      },
      submitError(response) {
        cd.message({ type: 'danger', message: Translator.trans(response.responseJSON.error.message) });
      }
    });
  }
}

export default Message;