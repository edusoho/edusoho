import {createApp} from 'vue';
import {Form, Input} from 'ant-design-vue';
import WatermarkSetting from './WatermarkSetting.vue';
import {createStyleTag} from 'vue3/js/common';
import 'vue3/main.less';

const app = createApp(WatermarkSetting);
app.use(Form);
app.use(Input);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/admin-v2/system/course-setting/watermark-setting/index.css?${window.app.version}`);
}

app.mount('#watermark-setting-app');
