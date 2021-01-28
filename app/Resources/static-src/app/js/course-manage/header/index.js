import { publish } from 'app/common/widget/publish';

const info = {
  title: 'course.manage.publish_title',
  hint: 'course.manage.publish_hint',
  success: 'course.manage.publish_success_hint',
  fail: 'course.manage.publish_fail_hint'
};

publish('.js-course-publish-btn', info);
