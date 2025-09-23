import Index from './Index.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../common';
import {createApp} from 'vue';
import {Button, Form, Input, Select, TreeSelect ,Upload, Modal, Radio, Switch, Popover, Dropdown, Menu, DatePicker, Tag} from 'ant-design-vue';
import 'vue3/main.less';

const app = createApp(Index,
  {
    classroom: $('#vue3-basic-setting').data('classroom'),
    tags: $('#vue3-basic-setting').data('tags'),
    enableOrg: $('#vue3-basic-setting').data('enableOrg'),
    cover: $('#vue3-basic-setting').data('cover'),
    imageUploadUrl: $('#vue3-basic-setting').data('imageUploadUrl'),
    courseNum: $('#vue3-basic-setting').data('courseNum'),
    coursePrice: $('#vue3-basic-setting').data('coursePrice'),
    coinSetting: $('#vue3-basic-setting').data('coinSetting'),
    classroomExpiryRuleUrl: $('#vue3-basic-setting').data('classroomExpiryRuleUrl'),
    vipInstalled: $('#vue3-basic-setting').data('vipInstalled'),
    vipEnabled: $('#vue3-basic-setting').data('vipEnabled'),
    vipLevels: $('#vue3-basic-setting').data('vipLevels'),
    serviceTags: $('#vue3-basic-setting').data('serviceTags'),
    infoSaveUrl: $('#vue3-basic-setting').data('infoSaveUrl'),
  }
);

app.use(Button);
app.use(Form);
app.use(Input);
app.use(Select);
app.use(TreeSelect);
app.use(Upload);
app.use(Modal);
app.use(Radio);
app.use(Switch);
app.use(Popover);
app.use(Dropdown);
app.use(Menu);
app.use(DatePicker);
app.use(Tag);

setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/classroom-manage/base-setting/index.css?${window.app.version}`);
}

app.mount('#vue3-basic-setting');