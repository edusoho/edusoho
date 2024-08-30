import { apiClient } from './api-client';

export const ContractApi = {
  async search(params) {
    return apiClient.get(`/api/contract`, {params});
  },
  async create(params) {
    return await apiClient.post(`/api/contract`, params);
  },
  async uploadFile(params) {
    return apiClient.post('/api/files', params)
  },
  async delete(id) {
    return await apiClient.delete(`/api/contract/${id}`);
  },
  async searchSignature(params) {
    return await apiClient.get(`/api/signed_contract`, {params});
  },
  async getSignatureContent(id) {
    return await apiClient.get(`/api/signed_contract/${id}`);
  },
  async getContractWithHtml(id) {
    return await apiClient.get(`/api/contract/${id}?viewMode=html`);
  },
  async getContract(id) {
    return await apiClient.get(`/api/contract/${id}`);
  },
  async update(id, params) {
    return await apiClient.patch(`/api/contract/${id}`, params);
  },
}
