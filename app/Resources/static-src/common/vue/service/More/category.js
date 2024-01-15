import { apiClient } from 'common/vue/service/api-client.js';

export const More = {
  // 获取课程教师
  async getVipLevels() {
    return apiClient.get('/api/plugins/vip/vip_levels')
  },
  async getCourseCategories() {
    return apiClient.get('/api/categories/course')
  },
  async searchCourse(params) {
    return apiClient.get('/api/courses', { params })
  },
  async searchClassroom(params) {
    return apiClient.get('/api/classrooms', { params })
  },
  async getClassroomCategories(params) {
    return apiClient.get('/api/categories/classroom')
  }
}