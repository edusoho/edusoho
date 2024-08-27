import { createApp } from 'vue';
import MyContract from './MyContract.vue';
import { Button, Modal, Pagination, Empty } from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';
import i18n from './vue-lang';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';

const app = createApp(MyContract);

app.use(i18n);

app.use(Button);
app.use(Modal);
app.use(Pagination);
app.use(Empty);


setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/contract/index.css?${window.app.version}`);
}

app.mount('#my-contract');