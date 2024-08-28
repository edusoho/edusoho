import { apiClient } from 'common/vue/service/api-client';

export const SettingApi = {
  async get(type) {
    return apiClient.get(`/api/settings/${type}`);
  },
}
