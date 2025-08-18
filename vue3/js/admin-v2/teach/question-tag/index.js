import { createApp } from 'vue';
import Index from './Index.vue';
import { Button, Divider, Input, Select, Popover, Form, Table, Dropdown, Menu, Badge, Popconfirm, Modal } from 'ant-design-vue';
// import 'ant-design-vue/dist/reset.css';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';

const app = createApp(Index);

app.use(Button);
app.use(Divider);
app.use(Input);
app.use(Select);
app.use(Popover);
app.use(Form);
app.use(Table);
app.use(Dropdown);
app.use(Menu);
app.use(Badge);
app.use(Popconfirm);
app.use(Modal);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/admin-v2/teach/question-tag/index.css?${window.app.version}`);
}

app.mount('#app');