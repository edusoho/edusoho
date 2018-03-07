import autocomplete from 'common/autocomplete';

let $form = $('#message-create-form');
let validator = $form.validate({
  rules: {
    'message[receiver]': {
      required: true,
      es_remote: {}
    },
    'message[content]': {
      required: true,
      maxlength: 500
    }
  }
});

autocomplete({
  element: '#message_receiver',
});