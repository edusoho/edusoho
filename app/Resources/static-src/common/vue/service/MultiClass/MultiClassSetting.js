import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassSetting = _.assignIn({
  // async get() {
  //   return apiClient.get(`/api/multi_class_settings`)
  // },
  async get() {
    return {
      group_number_limit: "100",
      assistant_service_limit: "250",
      review_time_limit: "24"
    }
  },
  async save(params) {
    return apiClient.post(`/api/multi_class_settings`, { params })
  },

})
