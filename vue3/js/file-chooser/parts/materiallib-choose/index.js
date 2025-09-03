import {createApp} from 'vue';
import {TreeSelect} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import Index from './Index.vue';

const app = createApp(Index);

app.use(TreeSelect);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
    createStyleTag(`/static-dist/vue3/js/file-chooser/parts/materiallib-choose/index.css?${window.app.version}`);
}

app.mount('#ant-category-select');
