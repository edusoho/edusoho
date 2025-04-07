import {createApp} from 'vue';
import 'vue3/main.less';
import { createStyleTag, setCurrentPrimaryColor } from 'vue3/js/common';
import { Button, Form, Switch, Select, Input, DatePicker, Popover, Alert, Spin } from 'ant-design-vue';
import AICompanionStudy from './AICompanionStudy.vue';

const app = createApp(AICompanionStudy, {
  courseId: $('#ai-companion-study').data('courseId'),
});

setCurrentPrimaryColor(app);

app.use(Button);
app.use(Form);
app.use(Switch);
app.use(Select);
app.use(Input);
app.use(DatePicker);
app.use(Popover);
app.use(Alert);
app.use(Spin);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/course-manage/ai-companion-study/index.css?${window.app.version}`);
}

app.mount('#ai-companion-study')