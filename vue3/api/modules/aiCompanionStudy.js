import {apiClient} from '../api-client';

export default {
  async getAgentConfig(courseId) {
    return apiClient.get(`/agent_config/${courseId}`);
  },
  async getDomains(courseId) {
    return apiClient.get(`/domains/${courseId}`);
  },
  async createAgentConfig(params) {
    return apiClient.post('/agent_config', params);
  },
  async updateAgentConfig(agentConfigId, params) {
    return apiClient.patch(`/agent_config/${agentConfigId}`, params);
  },
}