import {createApp} from 'vue';
import {Button, Divider} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import LiveOpen from './LiveOpen.vue';

const app = createApp(LiveOpen, {
  course: $('#live-open').data('course'),
});

app.use(Button);
app.use(Divider);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course/mobile/open-course/live-open/index.css?${window.app.version}`);
}

app.mount('#live-open');
