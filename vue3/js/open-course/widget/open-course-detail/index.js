import {createApp} from 'vue';
import {Button, Tabs, Empty, Spin, Divider} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import Index from './Index.vue';
import i18n from './vue-lang';

const app = createApp(Index, {
  course: $('#vue3-open-course-detail').data('course'),
  as: $('#vue3-open-course-detail').data('as'),
});

app.use(i18n);

app.use(Button);
app.use(Tabs);
app.use(Empty);
app.use(Spin);
app.use(Divider);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course/widget/open-course-detail/index.css?${window.app.version}`);
}

app.mount('#vue3-open-course-detail');