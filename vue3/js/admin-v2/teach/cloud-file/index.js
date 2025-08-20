import {createApp} from 'vue';
import {TreeSelect, Modal} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import CategoryTreeSelect from './CategoryTreeSelect.vue';

const app = createApp(CategoryTreeSelect);

app.use(TreeSelect);
app.use(Modal);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
    createStyleTag(`/static-dist/vue3/js/admin-v2/teach/cloud-file/index.css?${window.app.version}`);
}

app.mount('#ant-category');
