import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/live_replay';

export const LiveReplay = {
  async get({ params }) {
    return apiClient.get(`${baseUrl}`, { params });
  },

  async delete({ data }) {
    return apiClient.delete(`${baseUrl}`, { data });
  },

  async update({ query, params }) {
    return apiClient.patch(`${baseUrl}/${query.id}`, { params });
  }
}
