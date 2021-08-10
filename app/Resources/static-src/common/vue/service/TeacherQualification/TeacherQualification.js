import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/teacher_qualification';

export const TeacherQualification = {
  // 上传教师资质
  async add(params) {
    return apiClient.post(`${baseUrl}`, params)
  },

  // 教师资质展示页
  async search(params) {
    return apiClient.get(`${baseUrl}`, { params })
  }
}
