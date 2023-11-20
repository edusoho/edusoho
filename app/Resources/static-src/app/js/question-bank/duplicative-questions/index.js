import DuplicativeQuestions from './index.vue';
import qs from 'qs';
import { Button } from '@codeages/design-vue';
  
Vue.use(Button)
Vue.prototype.$qs = qs;

Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

new Vue({
  el: '#app',
  render: createElement => createElement(DuplicativeQuestions)
})

const wechatIntro = () => {
    introJs().setOptions({
      steps: [{
        element: '.duplicate-question',
        intro: Translator.trans('course.intro.wechat_subscribe'),
      }],
      doneLabel: '确认',
      showBullets: false,
      showStepNumbers: false,
      exitOnEsc: false,
      exitOnOverlayClick: false,
      tooltipClass: 'duplicate-intro',
    }).start();
  }

  
    wechatIntro();