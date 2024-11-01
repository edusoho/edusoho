import Axios from 'axios';
import * as ElementUI from 'element-ui';
import {Dropdown, Menu} from '@codeages/design-vue';
import ManageInfo from './index.vue';

import qs from 'qs';

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});

Vue.prototype.$axios = axios;
Vue.prototype.$qs = qs;

Vue.use(ElementUI);
Vue.use(Dropdown);
Vue.use(Menu);
Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

let $app = $('#app');

new Vue({
  render: createElement => createElement(ManageInfo, {
    props: {
      classroom: $app.data('classroom'),
      tags: $app.data('tags'),
      enableOrg: $app.data('enableOrg'),
      cover: $app.data('cover'),
      coverCropUrl: $app.data('coverCropUrl'),
      coverUploadToken: $app.data('coverUploadToken'),
      imageUploadUrl: $app.data('imageUploadUrl'),
      flashUploadUrl: $app.data('flashUploadUrl'),
      classroomLabel: $app.data('classroomLabel'),
      classroomExpiryRuleUrl: $app.data('classroomExpiryRuleUrl'),
      serviceTags: $app.data('serviceTags'),
      vipInstalled: $app.data('vipInstalled'),
      vipEnabled: $app.data('vipEnabled'),
      vipLevels: $app.data('vipLevels'),
      courseNum: $app.data('courseNum'),
      coinSetting: $app.data('coinSetting'),
      coinPrice: $app.data('coinPrice'),
      coursePrice: $app.data('coursePrice'),
      infoSaveUrl: $app.data('infoSaveUrl'),
    }
  })
}).$mount('#app');