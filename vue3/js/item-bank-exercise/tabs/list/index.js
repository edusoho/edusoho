import ChapterList from './ChapterList.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../../common';
import {createApp} from 'vue';
import {Button, Empty} from 'ant-design-vue';
import 'vue3/main.less';

const app = createApp(ChapterList,
  {
    categoryTree: $('#chapter-list').data('categoryTree'),
    records: $('#chapter-list').data('records'),
    previewAs: $('#chapter-list').data('previewAs'),
    member: $('#chapter-list').data('member'),
    exercise: $('#chapter-list').data('exercise'),
  }
);

app.use(Button);
app.use(Empty);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/item-bank-exercise/tabs/list/index.css?${window.app.version}`);
}

app.mount('#chapter-list');