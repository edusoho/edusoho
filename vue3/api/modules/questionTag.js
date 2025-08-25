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
  async search(params) {
    return [
      {
        id: '1',
        seq: 1,
        name: '111111',
        num: 10,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '2',
        seq: 2,
        name: '22222',
        num: 2,
        createTime: '1730447913',
        state: 'disable',
      },
      {
        id: '3',
        seq: 3,
        name: '33333',
        num: 3,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '4',
        seq: 4,
        name: '44444',
        num: 4,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '5',
        seq: 5,
        name: '5555555',
        num: 5,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '11',
        seq: 11,
        name: '111111',
        num: 10,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '21',
        seq: 21,
        name: '22222',
        num: 2,
        createTime: '1730447913',
        state: 'disable',
      },
      {
        id: '31',
        seq: 31,
        name: '33333',
        num: 3,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '41',
        seq: 41,
        name: '44444',
        num: 4,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '51',
        seq: 51,
        name: '5555555',
        num: 5,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '11',
        seq: 11,
        name: '111111',
        num: 10,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '12',
        seq: 12,
        name: '22222',
        num: 2,
        createTime: '1730447913',
        state: 'disable',
      },
      {
        id: '13',
        seq: 13,
        name: '33333',
        num: 3,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '14',
        seq: 14,
        name: '44444',
        num: 4,
        createTime: '1730447913',
        state: 'enable',
      },
      {
        id: '15',
        seq: 15,
        name: '5555555',
        num: 5,
        createTime: '1730447913',
        state: 'enable',
      },
    ];
  },
  async enableTag(params) {

  },
  async disableTag(params) {

  },
  async deleteTag(params) {

  },
};