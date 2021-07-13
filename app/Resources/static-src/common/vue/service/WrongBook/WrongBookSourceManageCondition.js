import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookSourceManageCondition = {
  // 错题分类级联查询条件
  async get(apiParams) {
    const params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.targetType}/source_manage/${apiParams.query.targetId}/condition`, { params });
  }
}
