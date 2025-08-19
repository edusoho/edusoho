import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Form, Switch, Select, Input, Spin } from 'ant-design-vue';
import Index from './Index.vue';

const app = createApp(Index, {
  itemBankId: $('#ai-companion-study').data('itemBankId'),
});

setCurrentPrimaryColor(app);

app.use(Button);
app.use(Form);
app.use(Switch);
app.use(Select);
app.use(Input);
app.use(Spin);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/item-bank-exercise-manage/ai-companion-study/index.css?${window.app.version}`);
}

app.mount('#ai-companion-study')