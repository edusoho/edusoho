import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/course';

export const Course = {
  // 获取课程教师
  async getTeacher(courseId, params) {
    return apiClient.get(`${baseUrl}/${courseId}/member`, params)
  },

  // 获取班课课时列表
  async getCourseLesson(courseId, params) {
    return apiClient.get(`${baseUrl}/${courseId}/item_with_lesson_v2`, { params })
  }
}
