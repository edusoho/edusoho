import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/open_course';

export const OpenCourse = {
  async search({ params }) {
    return apiClient.get(`${baseUrl}`, { params })
  }
}