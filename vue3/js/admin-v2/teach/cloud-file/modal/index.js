import {createApp} from 'vue';
import {TreeSelect, Modal, Input, Table, Button, Pagination} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import Index from './Index.vue';
import {emitter} from 'vue3/js/event-bus';
window.emitter = emitter;


const app = createApp(Index, {
    emitter: emitter,
});

app.use(TreeSelect);
app.use(Modal);
app.use(Input);
app.use(Table);
app.use(Button);
app.use(Pagination);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
    createStyleTag(`/static-dist/vue3/js/admin-v2/teach/cloud-file/modal/index.css?${window.app.version}`);
}

app.mount('#vue3-modal');