import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/course_tag';

export const CourseTag = {
  async get() {
    return apiClient.get(`${baseUrl}`);
  }
}
