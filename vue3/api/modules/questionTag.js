import {apiClient} from '../api-client';

export default {
  async isTagExists(params) {
    return false;
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
    ]
  },
  async createTag(params) {

  },
  async enableTag(params) {

  },
  async disableTag(params) {

  },
  async deleteTag(params) {

  },
}