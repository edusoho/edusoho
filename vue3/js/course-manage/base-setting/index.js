import ManageInfo from './ManageInfo.vue';
import { createStyleTag, setCurrentPrimaryColor } from '../../common';
import {createApp} from 'vue';
import {Button, Form, Input, Select, TreeSelect, Radio, Popover, Checkbox, List, Tooltip, Tag, DatePicker, Upload, Switch, Dropdown, Menu, Modal} from 'ant-design-vue';
import 'vue3/main.less';

const app = createApp(ManageInfo,
  {
    course: $('#manage-info').data('course'),
    courseSet: $('#manage-info').data('courseSet'),
    isUnMultiCourseSet: $('#manage-info').data('isUnMultiCourseSet'),
    lessonWatchLimit: $('#manage-info').data('lessonWatchLimit'),
    hasRoleAdmin: $('#manage-info').data('hasRoleAdmin'),
    wechatSetting: $('#manage-info').data('wechatSetting'),
    hasWechatNotificationManageRole: $('#manage-info').data('hasWechatNotificationManageRole'),
    hasMulCourses: $('#manage-info').data('hasMulCourses'),
    wechatManageUrl: $('#manage-info').data('wechatManageUrl'),
    liveCapacityUrl: $('#manage-info').data('liveCapacityUrl'),
    contentCourseRuleUrl: $('#manage-info').data('contentCourseRuleUrl'),
    canFreeTasks: $('#manage-info').data('canFreeTasks'),
    freeTasks: $('#manage-info').data('freeTasks'),
    taskName: $('#manage-info').data('taskName'),
    activityMetas: $('#manage-info').data('activityMetas'),
    courseRemindSendDays: $('#manage-info').data('courseRemindSendDays'),
    uploadMode: $('#manage-info').data('uploadMode'),
    serviceTags: $('#manage-info').data('serviceTags'),
    audioServiceStatus: $('#manage-info').data('audioServiceStatus'),
    videoConvertCompletion: $('#manage-info').data('videoConvertCompletion'),
    courseSetManageFilesUrl: $('#manage-info').data('courseSetManageFilesUrl'),
    courseProduct: $('#manage-info').data('courseProduct'),
    notifies: $('#manage-info').data('notifies'),
    canModifyCoursePrice: $('#manage-info').data('canModifyCoursePrice'),
    buyBeforeApproval: $('#manage-info').data('buyBeforeApproval'),
    canFreeActivityTypes: $('#manage-info').data('canFreeActivityTypes'),
    freeTaskChangelog: $('#manage-info').data('freeTaskChangelog'),
    courseManageUrl: $('#manage-info').data('courseManageUrl'),
    tags: $('#manage-info').data('tags'),
    imageSaveUrl: $('#manage-info').data('imageSaveUrl'),
    imageSrc: $('#manage-info').data('imageSrc'),
    imageUploadUrl: $('#manage-info').data('imageUploadUrl'),
    vipInstalled: $('#manage-info').data('vipInstalled'),
    vipEnabled: $('#manage-info').data('vipEnabled'),
    vipLevels: $('#manage-info').data('vipLevels'),
    enableOrg: $('#manage-info').data('enableOrg'),
  }
);

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

app.mount('#manage-info');