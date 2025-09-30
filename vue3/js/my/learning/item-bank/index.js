import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Tooltip, Pagination, Empty } from 'ant-design-vue';
import Index from './Index.vue';
import i18n from './vue-lang';

const app = createApp(Index);

setCurrentPrimaryColor(app);

app.use(i18n);

app.use(Tooltip);
app.use(Button);
app.use(Pagination);
app.use(Empty);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/my/learning/item-bank/index.css?${window.app.version}`);
}

app.mount('#vue3-item-bank')