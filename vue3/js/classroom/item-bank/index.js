import {createApp} from 'vue';
import 'vue3/main.less';
import ItemBankBind from '../../components/item-bank/ItemBankBind.vue';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Tooltip } from 'ant-design-vue';

const app = createApp(ItemBankBind, {
  bindType: 'classroom',
  bindId: $('#item-bank').data('classroomId'),
});

setCurrentPrimaryColor(app);

app.use(Button);
app.use(Tooltip);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#item-bank')