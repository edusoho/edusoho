import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/live_replay';

export const LiveReplay = {
  // 错题分类级联查询条件
  async get() {
    return apiClient.get(`${baseUrl}`);
  }
}
