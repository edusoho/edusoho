import {apiClient} from '../api-client';

export default {
  async follow(userId) {
    return apiClient.post('/me/friend', {userId});
  },
  async unfollow(userId) {
    return apiClient.delete(`/me/friend/${userId}`);
  },
  async getFollowStatus(toIds) {
    return apiClient.get('/me/friendship', {params: {toIds}});
  },
  async getPermissions(permissions) {
    return apiClient.get('/me/permission', {params: {permissions}});
  },
};
