import {apiClient} from '../api-client';

export default {
  async search(params) {
    return apiClient.get('/contracts', {params});
  },
  async create(params) {
    return apiClient.post('/contract', params);
  },
  async get(id) {
    return apiClient.get(`/contract/${id}`);
  },
  async update(id, params) {
    return apiClient.patch(`/contract/${id}`, params);
  },
  async delete(id) {
    return apiClient.delete(`/contract/${id}`);
  },
  async searchSignedRecords(params) {
    return apiClient.get('/signed_contracts', {params});
  },
  async getContractWithHtml(id) {
    return apiClient.get(`/contract/${id}?viewMode=html`);
  },
  async getContractSignTemplate(id, goodsKey) {
    return apiClient.get(`/contract/${id}/sign/${goodsKey}?viewMode=html`);
  },
  async sign(id, params) {
    return apiClient.post(`/contract/${id}/sign`, params);
  },
  async getMyContracts(params) {
    return apiClient.get('/me/contracts', {params});
  },
  async getSignedContract(id) {
    return apiClient.get(`/signed_contract/${id}`);
  },
};
