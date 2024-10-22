import { createApp } from 'vue';
import ContractList from './ContractList.vue';
import { Button, Modal, Pagination, Empty } from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';

const app = createApp(ContractList);

app.use(Button);
app.use(Modal);
app.use(Pagination);
app.use(Empty);


setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my-contract/index.css?${window.app.version}`);
}

app.mount('#my-contract');