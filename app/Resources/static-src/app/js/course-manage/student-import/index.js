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

new Importer({
  rules: rules,
  messages: messages
});