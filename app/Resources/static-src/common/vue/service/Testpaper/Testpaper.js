import {apiClient} from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService';

const baseUrl = '/api/assessment';
const baseService = new BaseService({baseUrl});

export const Testpaper = _.assignIn(baseService, {
  async search(params) {
    return apiClient.get(`/api/assessment`, {params});
  },
  async changeStatus(id, status) {
    return apiClient.post(`/api/assessment/${id}/status`, {status});
  },
});
