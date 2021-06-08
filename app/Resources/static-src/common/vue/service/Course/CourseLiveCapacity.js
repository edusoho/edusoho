import { apiClient } from 'common/vue/service/api-client.js';

export const CourseLiveCapacity = {
  async search(params) {
    return apiClient.get(`/api/course/${params.id}/live_capacity`, params)
  }
}
