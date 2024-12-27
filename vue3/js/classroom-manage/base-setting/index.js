import Index from './Index.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../common';
import {createApp} from 'vue';
import {Button, Form, Input, Select, TreeSelect ,Upload, Modal} from 'ant-design-vue';
import 'vue3/main.less';

const app = createApp(Index,
  {
    classroom: $('#manage-info').data('classroom'),
    tags: $('#manage-info').data('tags'),
    enableOrg: $('#manage-info').data('enableOrg'),
    cover: $('#manage-info').data('cover'),
    imageUploadUrl: $('#manage-info').data('imageUploadUrl'),
  }
);

app.use(Button);
app.use(Form);
app.use(Input);
app.use(Select);
app.use(TreeSelect);
app.use(Upload);
app.use(Modal);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/classroom-manage/base-setting/index.css?${window.app.version}`);
}

app.mount('#manage-info');