import {apiClient} from '../api-client';

export default {
  async createLesson(courseId, params) {
    return apiClient.post(`/open_course/${courseId}/lesson`, params);
  },
  async findLesson(courseId) {
    return apiClient.get(`/open_course/${courseId}/lesson`);
  },
}