import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/assistants';
const baseService = new BaseService({ baseUrl })

export const Assistant = _.assignIn(baseService, {
  async add(params) {
    return apiClient.post(baseUrl, params);
  },
})
