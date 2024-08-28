import { apiClient } from './api-client';

export const MyContractApi = {
  async getMyContracts(params) {
    return await apiClient.get(`/api/me/contract`, {params});
  },
  async getSignedContract(id) {
    return await apiClient.get(`/api/signed_contract/${id}`);
  },
}