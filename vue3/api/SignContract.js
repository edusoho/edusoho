import { apiClient } from './api-client';

export const SignContractApi = {
  async getContractTemplate(id, goodsKey) {
    return await apiClient.get(`/api/contract/${id}/sign/${goodsKey}?viewMode=html`);
  },
  async getContract(id) {
    return await apiClient.get(`/api/contract/${id}`);
  },
  async signContract(id, params) {
    return await apiClient.post(`/api/contract/${id}/sign`, params);
  },
}