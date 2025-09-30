import {ajaxClient} from '../api-client';

export default {
  async updateCourseSet(courseSetId, courseId, params) {
    return ajaxClient.post(`/course_set/${courseSetId}/manage/course/${courseId}/info`, new URLSearchParams(params));
  },
};