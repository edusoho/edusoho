import Importer from 'app/common/importer';

let rules = {
  'remark': {
    maxlength: 50,
  },
  'price': {
    currency: true
  }
};

let messages = {};

if ($('#min-price').length) {
  rules.price = Object.assign({
    required: true,
    min: parseFloat($('#min-price').data('price'))
  }, rules.price);
  messages.price = Object.assign({min: Translator.trans('item_bank_exercise.student_create.price_min_error_hint')}, messages.price);
}

if ($('#min-price').length) {
  rules.price = Object.assign({
    required: true,
    min: parseFloat($('#min-price').data('price'))
  }, rules.price);
  messages.price = Object.assign({min: Translator.trans('item_bank_exercise.student_create.price_min_error_hint')}, messages.price);
}

new Importer({
  rules: rules,
  messages: messages
});