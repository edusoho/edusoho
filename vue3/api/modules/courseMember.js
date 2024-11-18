import {apiClient, ajaxClient} from '../api-client';

export default {
  async search(courseId, params) {
    return apiClient.get(`/course/${courseId}/members`, {params});
  },
  async remove(courseSetId, courseId, userId) {
    return ajaxClient.post(`/course_set/${courseSetId}/manage/course/${courseId}/students/${userId}/remove`);
  },
  async batchRemove(courseSetId, courseId, studentIds) {
    return ajaxClient.post(`/course_set/${courseSetId}/manage/course/${courseId}/students/remove`, new URLSearchParams({studentIds}));
  },
};
