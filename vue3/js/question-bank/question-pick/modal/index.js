import {createApp} from 'vue';
import SearchForm from './SearchForm.vue';
import {Button, Dropdown, Menu, Select, Input, Modal, Empty} from 'ant-design-vue';
import 'vue3/main.less';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';

const app = createApp(SearchForm);

app.use(Button);
app.use(Dropdown);
app.use(Menu);
app.use(Select);
app.use(Input);
app.use(Modal);
app.use(Empty);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/question-bank/question-pick/modal/index.css?${window.app.version}`);
}

app.mount('#vue3-app');