import Index from './Index.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../common';
import {createApp} from 'vue';
import {Button} from 'ant-design-vue';
import 'vue3/main.less';

const app = createApp(Index,
  {
    classroom: $('#manage-info').data('classroom'),
  }
);

app.use(Button);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/classroom-manage/base-setting/index.css?${window.app.version}`);
}

app.mount('#manage-info');