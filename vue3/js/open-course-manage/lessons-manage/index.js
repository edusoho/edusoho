import {createApp} from 'vue';
import {Button, Empty} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import LessonsManage from './LessonsManage.vue';

const app = createApp(LessonsManage, {
  course: $('#lessons-manage').data('course')
});

app.use(Button);
app.use(Empty);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course-manage/lessons-manage/index.css?${window.app.version}`);
}

app.mount('#lessons-manage');