import Index from './Index.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../common';
import {createApp} from 'vue';
import {Button, Form, Input, Select, TreeSelect, Radio, Popover, Checkbox, List, Tooltip, Tag, DatePicker, Upload, Switch, Dropdown, Menu, Modal} from 'ant-design-vue';
import 'vue3/main.less';
import i18n from './vue-lang';

const app = createApp(Index,
  {
    course: $('#vue3-basic-setting').data('course'),
    courseSet: $('#vue3-basic-setting').data('courseSet'),
    isUnMultiCourseSet: $('#vue3-basic-setting').data('isUnMultiCourseSet'),
    lessonWatchLimit: $('#vue3-basic-setting').data('lessonWatchLimit'),
    hasRoleAdmin: $('#vue3-basic-setting').data('hasRoleAdmin'),
    wechatSetting: $('#vue3-basic-setting').data('wechatSetting'),
    hasWechatNotificationManageRole: $('#vue3-basic-setting').data('hasWechatNotificationManageRole'),
    hasMulCourses: $('#vue3-basic-setting').data('hasMulCourses'),
    wechatManageUrl: $('#vue3-basic-setting').data('wechatManageUrl'),
    liveCapacityUrl: $('#vue3-basic-setting').data('liveCapacityUrl'),
    contentCourseRuleUrl: $('#vue3-basic-setting').data('contentCourseRuleUrl'),
    canFreeTasks: $('#vue3-basic-setting').data('canFreeTasks'),
    freeTasks: $('#vue3-basic-setting').data('freeTasks'),
    taskName: $('#vue3-basic-setting').data('taskName'),
    activityMetas: $('#vue3-basic-setting').data('activityMetas'),
    courseRemindSendDays: $('#vue3-basic-setting').data('courseRemindSendDays'),
    uploadMode: $('#vue3-basic-setting').data('uploadMode'),
    serviceTags: $('#vue3-basic-setting').data('serviceTags'),
    audioServiceStatus: $('#vue3-basic-setting').data('audioServiceStatus'),
    videoConvertCompletion: $('#vue3-basic-setting').data('videoConvertCompletion'),
    courseSetManageFilesUrl: $('#vue3-basic-setting').data('courseSetManageFilesUrl'),
    courseProduct: $('#vue3-basic-setting').data('courseProduct'),
    notifies: $('#vue3-basic-setting').data('notifies'),
    canModifyCoursePrice: $('#vue3-basic-setting').data('canModifyCoursePrice'),
    buyBeforeApproval: $('#vue3-basic-setting').data('buyBeforeApproval'),
    canFreeActivityTypes: $('#vue3-basic-setting').data('canFreeActivityTypes'),
    freeTaskChangelog: $('#vue3-basic-setting').data('freeTaskChangelog'),
    courseManageUrl: $('#vue3-basic-setting').data('courseManageUrl'),
    tags: $('#vue3-basic-setting').data('tags'),
    imageSaveUrl: $('#vue3-basic-setting').data('imageSaveUrl'),
    imageSrc: $('#vue3-basic-setting').data('imageSrc'),
    imageUploadUrl: $('#vue3-basic-setting').data('imageUploadUrl'),
    vipInstalled: $('#vue3-basic-setting').data('vipInstalled'),
    vipEnabled: $('#vue3-basic-setting').data('vipEnabled'),
    vipLevels: $('#vue3-basic-setting').data('vipLevels'),
    enableOrg: $('#vue3-basic-setting').data('enableOrg'),
  }
);

app.use(i18n);

app.use(Button);
app.use(Form);
app.use(Input);
app.use(Select);
app.use(TreeSelect);
app.use(Radio);
app.use(Popover);
app.use(Checkbox);
app.use(List);
app.use(Tooltip);
app.use(Tag);
app.use(DatePicker);
app.use(Upload);
app.use(Switch);
app.use(Dropdown);
app.use(Menu);
app.use(Modal);


setCurrentPrimaryColor(app);

if (process.env.NODE_ENV === 'production') {
  createStyleTag(`/static-dist/vue3/js/course-manage/base-setting/index.css?${window.app.version}`);
}

app.mount('#vue3-basic-setting');