import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/multi_class_product';

export const MultiClassProduct = {
  // 新增产品
  async add(params) {
    return apiClient.post(baseUrl, params)
  },
  // 更新产品
  async update(params) {
    return apiClient.put(baseUrl, params)
  },
  // 产品列表
  async search(params) {
    return apiClient.get(baseUrl, params)
  },
  // 删除产品
  async delete({ id }) {
    return apiClient.post(`${baseUrl}/${id}`)
  },
}