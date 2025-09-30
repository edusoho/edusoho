import {createApp} from 'vue';
import 'vue3/main.less';
import Index from '../../components/item-bank/Index.vue';
import i18n from '../../components/item-bank/vue-lang';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Empty, Drawer, TreeSelect, Select, Input, Table, Checkbox, Tooltip, Spin } from 'ant-design-vue';

const app = createApp(Index, {
  bindType: 'course',
  bindId: $('#vue3-item-bank').data('courseId'),
});

setCurrentPrimaryColor(app);

app.use(i18n);

app.use(Button);
app.use(Empty);
app.use(Drawer);
app.use(TreeSelect);
app.use(Select);
app.use(Input);
app.use(Table);
app.use(Checkbox);
app.use(Tooltip);
app.use(Spin);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/course-manage/item-bank/index.css?${window.app.version}`);
}

app.mount('#vue3-item-bank')