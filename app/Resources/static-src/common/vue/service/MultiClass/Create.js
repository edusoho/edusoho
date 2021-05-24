import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/me';

export const Create = {
  // 获取课程
  async teachCourses(params) {
    return apiClient.get(`${baseUrl}/teach_courses`)
  },

  // 获取课程教师
  async teacher(courseId, params) {
    return apiClient.get(`/api/course/${courseId}/member`, params)
  },

  // 获取助教列表
  async assistants() {
    return apiClient.get(`/api/assistants`)
  },

   // 产品列表
   async products() {
    return apiClient.get(`/api/multi_class_product`)
  }
}
