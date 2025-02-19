import { apiClient } from 'common/vue/service/api-client.js';

export const More = {
  async getVipLevels() {
    return apiClient.get('/api/plugins/vip/vip_levels')
  },
  async getVip() {
    return apiClient.get('/api/settings/vip')
  },
  async getCourseCategories() {
    return apiClient.get('/api/categories/course')
  },
  async searchCourse(params) {
    return apiClient.get('/api/course_set', { params })
  },
  async searchClassroom(params) {
    return apiClient.get('/api/classrooms', { params })
  },
  async getClassroomCategories(params) {
    return apiClient.get('/api/categories/classroom')
  }
}