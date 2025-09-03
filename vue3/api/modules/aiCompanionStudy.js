import {apiClient} from '../api-client';

export default {
  async getAgentConfig(courseId) {
    return apiClient.get(`/agent_config/${courseId}`);
  },
  async getDomains() {
    return apiClient.get('/domains');
  },
  async getDomainId(params) {
    return apiClient.post('/domain_match', params);
  },
  async createAgentConfig(params) {
    return apiClient.post('/agent_config', params);
  },
  async updateAgentConfig(courseId, params) {
    return apiClient.patch(`/agent_config/${courseId}`, params);
  },
  async getAgentStatus() {
    return apiClient.get(`/agent/status`);
  },
}