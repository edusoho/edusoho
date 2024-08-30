import { createI18n } from 'vue-i18n';
import { merge } from 'lodash-es';

const i18n = createI18n(merge({
  legacy: false,
  locale: window.app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      btn: {
        submit: '立即提交'
      }
    },
    en: {
      btn: {
        submit: 'Submit'
      }
    }
  },
}, {}));

export default i18n;
