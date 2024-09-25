import { createApp } from 'vue';
import SignContract from './SignContract.vue';
import { Button, Form, Input, Modal, Divider } from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';
import 'vue3/main.less';
import i18n from './vue-lang';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';

const app = createApp(SignContract);

app.use(i18n);

app.use(Button);
app.use(Form);
app.use(Input);
app.use(Modal);
app.use(Divider);


setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/sign-contract/index.css?${window.app.version}`);
}

app.mount('#sign-contract-modal');