import { apiClient } from 'common/vue/service/api-client';

export const ContractApi = {
  async search(params) {
    return apiClient.get(`/api/plugins/electronicContract/contract`, {params});
  },
  async create(params) {
    return await apiClient.post(`/api/plugins/electronicContract/contract`, params);
  },
  async uploadFile(params) {
    return apiClient.post('/api/files', params)
  },
  async delete(id) {
    return await apiClient.delete(`/api/plugins/electronicContract/contract`, {params: {id}});
  },
  async searchSignature(params) {
    return await apiClient.get(`/api/signed_contract`, {params});
  },
  async getSignatureContent(id) {
    return await apiClient.get(`/api/signed_contract/${id}`);
  },
}
