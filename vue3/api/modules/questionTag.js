import {apiClient} from '../api-client';

export default {
  async isGroupNameExists(params) {
    return apiClient.post('/question_tag_group_name_check', params);
  },
  async isNameExists(params) {
    return apiClient.post('/question_tag_name_check', params);
  },
  async createTagGroup(params) {
    return apiClient.post('/question_tag_group', params);
  },
  async createTag(params) {
    return apiClient.post('/question_tag', params);
  },
  async searchTagGroup(params) {
    return apiClient.get('/question_tag_group', {params});
  },
  async searchTag(params) {
    return apiClient.get('/question_tag', {params});
  },
  async updateTagGroup(id, params) {
    return apiClient.patch(`/question_tag_group/${id}`, params);
  },
  async updateTag(id, params) {
    return apiClient.patch(`/question_tag/${id}`, params);
  },
  async deleteTagGroup(id) {
    return apiClient.delete(`/question_tag_group/${id}`);
  },
  async deleteTag(id) {
    return apiClient.delete(`/question_tag/${id}`);
  },
};