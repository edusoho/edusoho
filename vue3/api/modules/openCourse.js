import {apiClient} from '../api-client';

export default {
  async createLesson(courseId, params) {
    return apiClient.post(`/open_course/${courseId}/lesson`, params);
  },
  async fetchLessons(courseId) {
    return apiClient.get(`/open_course/${courseId}/lesson`);
  },
  async deleteLesson(courseId, lessonId) {
    return apiClient.delete(`/open_course/${courseId}/lesson/${lessonId}`);
  },
  async publishLesson(courseId, lessonId) {
    return apiClient.post(`/open_course/${courseId}/lesson/${lessonId}/publish`);
  },
  async unpublishLesson(courseId, lessonId) {
    return apiClient.delete(`/open_course/${courseId}/lesson/${lessonId}/publish`);
  },
  async getLesson(courseId, lessonId) {
    return apiClient.get(`/open_course/${courseId}/lesson/${lessonId}`);
  },
}