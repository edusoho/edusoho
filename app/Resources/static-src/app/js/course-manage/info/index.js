import Intro from './intro';
// import Expiry from 'app/js/course-manage/expiry/expiry';
// import {initTags} from 'app/js/courseset-manage/base/tag';
import ManageInfo from './src/manage-info';
import ElementUI from 'element-ui';
import Axios from 'axios';

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/json',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});

Vue.prototype.$axios = axios;

Vue.use(ElementUI);
Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});


new Vue({
  el: '#app',
  render: createElement => createElement(ManageInfo, {
    props: {
      course: $('#app').data('course'),
      courseSet: $('#app').data('courseSet'),
      isUnMultiCourseSet: $('#app').date('isUnMultiCourseSet'),
      lessonWatchLimit: $('#app').data('lessonWatchLimit'),
      hasRoleAdmin: $('#app').data('hasRoleAdmin'),
      wechatSetting: $('#app').data('wechatSetting'),
      hasWechatNotificationManageRole: $('#app').data('hasWechatNotificationManageRole'),
      hasMulCourses: $('#app').data('hasMulCourses'),
      wechatManageUrl: $('#app').data('wechatManageUrl'),
      liveCapacityUrl: $('#app').data('liveCapacityUrl'),
      contentCourseRuleUrl: $('#app').data('contentCourseRuleUrl'),
      canFreeTasks: $('#app').data('canFreeTasks'),
      freeTasks: $('#app').data('freeTasks'),
      taskName: $('#app').data('taskName'),
      activityMetas: $('#app').data('activityMetas'),
      courseRemindSendDays: $('#app').data('courseRemindSendDays'),
      uploadMode: $('#app').data('uploadMode'),
      serviceTags: $('#app').data('serviceTags'),
      audioServiceStatus: $('#app').data('audioServiceStatus'),
      videoConvertCompletion: $('#app').data('videoConvertCompletion'),
      courseSetManageFilesUrl: $('#app').data('courseSetManageFilesUrl'),
      courseProduct: $('#app').data('courseProduct'),
      notifies: $('#app').data('notifies'),
      canModifyCoursePrice: $('#app').data('canModifyCoursePrice'),
      buyBeforeApproval: $('#app').data('buyBeforeApproval'),
      canFreeActivityTypes: $('#app').data('canFreeActivityTypes'),
      freeTaskChangelog: $('#app').data('freeTaskChangelog'),
      courseManageUrl: $('#app').data('courseManageUrl')
    },
  }),
});

class CourseInfo {
  constructor() {
//     if ($('#maxStudentNum-field').length > 0) {
//       $.get($('#maxStudentNum-field').data('liveCapacityUrl')).done((liveCapacity) => {
//         $('#maxStudentNum-field').data('liveCapacity', liveCapacity.capacity);
//       });
//     }
    this.setIntroPosition();
  }

  setIntroPosition() {
    const space = 44;
    const introRight = $('.js-course-manage-info').offset().left + space;
    window.onload = () => {
      $('.js-plan-intro').css('right', `${introRight}px`).removeClass('hidden');
    };
  }

//   saveForm() {
//     $('#course-submit').on('click', (event) => {
//       this.expiry.commonExpiryMode();
//       const $summaryField = $('#courseset-summary-field');
//       if ($summaryField.length) {
//         $summaryField.val(this.editor.getData());
//       }
//       if (this.validator.form()) {
//         $('#course-info-form').submit();
//       }
//     });
//   }
}

//
new CourseInfo();
//
setTimeout(function () {
  new Intro();
}, 500);