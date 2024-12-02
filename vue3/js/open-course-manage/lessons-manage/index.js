import {createApp} from 'vue';
import {Button, Empty, Drawer, Form, Input, DatePicker, InputNumber, Switch, Select, Spin, Table, Pagination} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import LessonsManage from './LessonsManage.vue';

const app = createApp(LessonsManage, {
  course: $('#lessons-manage').data('course')
});

app.use(Button);
app.use(Empty);
app.use(Drawer);
app.use(Form);
app.use(Input);
app.use(DatePicker);
app.use(InputNumber);
app.use(Switch);
app.use(Select);
app.use(Spin);
app.use(Table);
app.use(Pagination);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/open-course-manage/lessons-manage/index.css?${window.app.version}`);
}

app.mount('#lessons-manage');