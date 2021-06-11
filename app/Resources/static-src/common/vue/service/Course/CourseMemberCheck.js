import { apiClient } from 'common/vue/service/api-client.js';

export const CourseMemberCheck = {
  async checkStudentName(courseId, params) {
    return apiClient.post(`/api/course/${courseId}/member_check`, params)
  },
}
