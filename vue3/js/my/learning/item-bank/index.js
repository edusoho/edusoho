import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Tooltip } from 'ant-design-vue';
import ItemBankList from './ItemBankList.vue';

const app = createApp(ItemBankList);

setCurrentPrimaryColor(app);

app.use(Tooltip);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#item-bank-list')