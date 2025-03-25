import {apiClient} from '../api-client';

export default {
  async getAgentConfig(courseId) {
    return apiClient.get(`/agent_config/${courseId}`);
  },
  async getDomains(courseId) {
    return apiClient.get(`/domains/${courseId}`);
  },
  async getDomainId(courseId) {
    return apiClient.post(`/domains/${courseId}/match`);
  },
  async createAgentConfig(params) {
    return apiClient.post('/agent_config', params);
  },
  async updateAgentConfig(courseId, params) {
    return apiClient.patch(`/agent_config/${courseId}`, params);
  },
}