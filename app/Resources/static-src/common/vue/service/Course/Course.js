import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/course';

export const Course = {
  // 获取课程教师
  async getTeacher(courseId, params) {
    return apiClient.get(`${baseUrl}/${courseId}/member`, params)
  }
}
