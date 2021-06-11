import Importer from 'app/common/importer';

let rules = {
  'remark': {
    maxlength: 50,
  },
  'price': {
    currency: true,
    max: parseFloat($('#buy-price').data('price')),
  }
};

let messages = {
  price: {
    max: Translator.trans('course_manage.student_create.price_max_error_hint'),
  }
};

new Importer({
  rules: rules,
  messages: messages
});