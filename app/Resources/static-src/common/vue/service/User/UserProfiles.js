import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/user_profiles';

export const UserProfiles = {
  async get(userId) {
    return apiClient.get(`${baseUrl}/${userId}`)
  }
}
