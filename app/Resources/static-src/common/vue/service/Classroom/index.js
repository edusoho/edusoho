import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/classrooms';

export const Classroom = {
  async search(params) {
    return apiClient.get(`${baseUrl}`, { params })
  },

  // 班级课程列表
  async getCourses({ query }) {
    return apiClient.get(`${baseUrl}/${query.classroomId}/courses`);
  }
}
