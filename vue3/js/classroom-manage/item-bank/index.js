import {createApp} from 'vue';
import 'vue3/main.less';
import ItemBankPage from '../../components/item-bank/ItemBankPage.vue';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Empty, Drawer, TreeSelect, Select, Input, Table, Checkbox, Tooltip } from 'ant-design-vue';

const app = createApp(ItemBankPage, {
  bindType: 'classroom',
  bindId: $('#item-bank').data('classroomId'),
});

setCurrentPrimaryColor(app);

app.use(Button);
app.use(Empty);
app.use(Drawer);
app.use(TreeSelect);
app.use(Select);
app.use(Input);
app.use(Table);
app.use(Checkbox);
app.use(Tooltip);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/classroom-manage/item-bank/index.css?${window.app.version}`);
}

app.mount('#item-bank')