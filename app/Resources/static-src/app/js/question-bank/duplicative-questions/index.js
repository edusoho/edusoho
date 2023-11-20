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


// const wechatIntro = () => {
//     introJs().setOptions({
//         steps: [{
//           element: '.duplicate-question',
//           intro: Translator.trans('upgrade.cloud.capabilities.to.experience'),
//           position: 'bottom',
//         },
//         {
//             element: '.question-head',
//             intro: Translator.trans('upgrade.cloud.capabilities.to.experience'),
//             position: 'bottom',
//           }],
//         doneLabel: '我知道了（2/2）',
//         nextLabel: '下一步（1/2）',
//         showBullets: false,
//         showStepNumbers: false,
//         exitOnEsc: false,
//         exitOnOverlayClick: false,
//         tooltipClass: '',
//       }).start()
//   }

  
//     wechatIntro();