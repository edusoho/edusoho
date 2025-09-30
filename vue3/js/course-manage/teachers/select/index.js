import {createApp} from 'vue';
import {Select} from 'ant-design-vue';
import TeacherSelect from './TeacherSelect.vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';

const app = createApp(TeacherSelect);
app.use(Select);
setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/course-manage/teachers/select/index.css?${window.app.version}`);
}

app.mount('#teacher-select-app');
