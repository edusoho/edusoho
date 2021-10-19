import { apiClient } from 'common/vue/service/api-client.js';
const baseUrl = '/api/live_statistic';

export const LiveStatistic = {
  // 课程直播统计页 - 直播列表
  async get(apiParams) {
    const params = apiParams.params;
    return apiClient.get(`${baseUrl}`, { params });
  }
};
