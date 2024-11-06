import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import {Modal, Button} from 'ant-design-vue';
import ItemBankGift from './ItemBankGift.vue';

const app = createApp(ItemBankGift, {
  exerciseBind: $('#item-bank-gift').data('exerciseBind'),
});

setCurrentPrimaryColor(app);

app.use(Modal);
app.use(Button);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/order/item-bank/index.css?${window.app.version}`);
}

app.mount('#item-bank-gift')