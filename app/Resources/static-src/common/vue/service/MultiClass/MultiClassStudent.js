import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassStudent = _.assignIn({
  async search(params) {
    return apiClient.get(`/api/multi_class/${params.id}/students`, { params })
  },
  async add(params) {
    return apiClient.post(`/api/multi_class/${params.id}/students`, params)
  }
})
