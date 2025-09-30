import {createApp} from 'vue';
import {Modal, Button, Empty} from 'ant-design-vue';
import {createStyleTag, setCurrentPrimaryColor} from 'vue3/js/common';
import 'vue3/main.less';
import Index from './Index.vue';
import {emitter} from 'vue3/js/event-bus';
window.emitter = emitter;

const app = createApp(Index);

app.use(Modal);
app.use(Button);
app.use(Empty);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/question-bank/question/modal/index.css?${window.app.version}`);
}

app.mount('#vue3-modal');