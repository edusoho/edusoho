import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookCondition = {
  // 错题分类级联查询条件
  async get(apiParams) {
    const params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.poolId}/condition`, { params });
  }
}
