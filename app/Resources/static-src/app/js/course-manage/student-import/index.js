import Importer from 'app/common/importer';

new Importer({
  rules: {
    'remark': {
      maxlength: 50,
    },
    'price': {
      currency: true
    }
  }
});