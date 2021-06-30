import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassAssistant = _.assignIn({
  async search(params) {
    return apiClient.get(`/api/multi_class/${params.id}/assistants`, { params })
  },
})
