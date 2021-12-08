import { apiClient } from 'common/vue/service/api-client.js';
const baseUrl = '/api/classroom';

export const Classroom = {
  // 班级课程列表
  async getCourses({ query }) {
    return apiClient.get(`${baseUrl}/${query.classroomId}/courses`);
  }
};
