import {createApp} from 'vue';
import 'vue3/main.less';
import ItemBankPage from './ItemBankPage.vue';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Empty, Drawer, TreeSelect, Select, Input, Table, Checkbox } from 'ant-design-vue';

const app = createApp(ItemBankPage);

setCurrentPrimaryColor(app);

app.use(Button);
app.use(Empty);
app.use(Drawer);
app.use(TreeSelect);
app.use(Select);
app.use(Input);
app.use(Table);
app.use(Checkbox);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#item-bank')