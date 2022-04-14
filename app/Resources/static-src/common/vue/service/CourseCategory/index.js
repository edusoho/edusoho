import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/course_category';

export const CourseCategory = {
  async get() {
    return apiClient.get(`${baseUrl}`);
  }
}
