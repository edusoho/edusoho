import {createApp} from 'vue';
import {Form, DatePicker, Select, Input, Table, Tooltip, Dropdown, Menu, Checkbox, Pagination} from 'ant-design-vue';
import StudentList from './StudentList.vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';

const app = createApp(StudentList);
app.use(Form);
app.use(DatePicker);
app.use(Select);
app.use(Input);
app.use(Table);
app.use(Tooltip);
app.use(Dropdown);
app.use(Menu);
app.use(Checkbox);
app.use(Pagination);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/classroom-manage/students/index.css?${window.app.version}`);
}

app.mount('#student-list-app');
