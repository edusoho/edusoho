import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Tooltip, Pagination, Empty } from 'ant-design-vue';
import ItemBankList from './ItemBankList.vue';

const app = createApp(ItemBankList);

setCurrentPrimaryColor(app);

app.use(Tooltip);
app.use(Button);
app.use(Pagination);
app.use(Empty);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#item-bank-list')