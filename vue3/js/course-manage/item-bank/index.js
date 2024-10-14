import {createApp} from 'vue';
import 'vue3/main.less';
import ItemBankList from './ItemBankList.vue';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button } from 'ant-design-vue';

const app = createApp(ItemBankList);

setCurrentPrimaryColor(app);

app.use(Button);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#item-bank')