import {createApp} from 'vue';
import {Button, Tabs, Empty, Spin, Divider} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import OpenCourseDetail from './OpenCourseDetail.vue';

const app = createApp(OpenCourseDetail, {
  course: $('#open-course-detail').data('course')
});

app.use(Button);
app.use(Tabs);
app.use(Empty);
app.use(Spin);
app.use(Divider);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course/widget/open-course-detail/index.css?${window.app.version}`);
}

app.mount('#open-course-detail');