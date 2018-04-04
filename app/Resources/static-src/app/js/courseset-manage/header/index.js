import { publish } from 'app/common/widget/publish';

const info = {
  title: 'course_set.manage.publish_title',
  hint: 'course_set.manage.publish_hint',
  success: 'course_set.manage.publish_success_hint',
  fail: 'course_set.manage.publish_fail_hint'
};

publish('.js-course-publish-btn', info);