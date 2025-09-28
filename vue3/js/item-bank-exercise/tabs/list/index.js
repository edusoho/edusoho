import Index from './Index.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../../common';
import {createApp} from 'vue';
import {Button, Empty} from 'ant-design-vue';
import 'vue3/main.less';
import i18n from './vue-lang';

const app = createApp(Index,
  {
    categoryTree: $('#vue3-item-bank-exercise-list').data('categoryTree'),
    records: $('#vue3-item-bank-exercise-list').data('records'),
    previewAs: $('#vue3-item-bank-exercise-list').data('previewAs'),
    member: $('#vue3-item-bank-exercise-list').data('member'),
    exercise: $('#vue3-item-bank-exercise-list').data('exercise'),
    moduleId: $('#vue3-item-bank-exercise-list').data('moduleId'),
  }
);

app.use(i18n);

app.use(Button);
app.use(Empty);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/item-bank-exercise/tabs/list/index.css?${window.app.version}`);
}

app.mount('#vue3-item-bank-exercise-list');