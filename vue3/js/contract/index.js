import { createApp } from 'vue';
import Index from './Index.vue';
import { Button, Form, Input, Menu, Select, Table, Pagination, Drawer, Anchor, Checkbox, Space, Popconfirm, Modal, Upload, Tabs, DatePicker, Tooltip } from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';
import 'vue3/main.less';
import router from './router';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';

const app = createApp(Index);

app.use(router);

app.use(Button);
app.use(Form);
app.use(Input);
app.use(Menu);
app.use(Select);
app.use(Table);
app.use(Pagination);
app.use(Drawer);
app.use(Anchor);
app.use(Checkbox);
app.use(Space);
app.use(Popconfirm);
app.use(Modal);
app.use(Upload);
app.use(Tabs);
app.use(DatePicker);
app.use(Tooltip);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/contract/index.css?${window.app.version}`);
}

app.mount('#app');