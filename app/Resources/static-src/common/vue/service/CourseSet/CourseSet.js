import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/course_set';

export const CourseSet = {
  // 创建课程
  async add(params) {
    return apiClient.post(baseUrl, params)
  }
}