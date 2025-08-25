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
  async enableTag(params) {

  },
  async disableTag(params) {

  },
  async deleteTag(params) {

  },
};