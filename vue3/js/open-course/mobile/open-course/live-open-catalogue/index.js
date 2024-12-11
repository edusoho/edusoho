import {createApp} from 'vue';
import {Button, Divider} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import LiveOpenCatalogue from './LiveOpenCatalogue.vue';

const app = createApp(LiveOpenCatalogue, {
  course: $('#live-open-catalogue').data('course'),
});

app.use(Button);
app.use(Divider);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course/mobile/open-course/live-open-catalogue/index.css?${window.app.version}`);
}

app.mount('#live-open-catalogue');
