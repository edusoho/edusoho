import {createApp} from 'vue';
import {Modal} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import ItemBankShowRemoveModal from './ItemBankShowRemoveModal.vue';

const app = createApp(ItemBankShowRemoveModal, {
  displayBindPopUp: $('#item-bank-show-remove-modal').data('displayBindPopUp')
});

app.use(Modal);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/item-bank-exercise/index.css?${window.app.version}`);
}

app.mount('#item-bank-show-remove-modal');