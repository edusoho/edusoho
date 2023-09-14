import { apiClient } from "common/vue/service/api-client";

const baseUrl = "/api/wrong_book";

export const WrongBook_pool = {
  // 错题数
  async get(apiParams) {
    let params = apiParams.params;
    return apiClient.get(`${baseUrl}/${apiParams.query.poolId}`, { params });
  }
};
