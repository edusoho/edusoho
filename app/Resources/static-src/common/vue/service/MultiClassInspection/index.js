import { apiClient } from 'common/vue/service/api-client.js';

export default {
  search ({ query = {}, params = {}, data = {} } = {}) {
    return apiClient.get('/api/multi_class_inspection', {  params })
  },

  getLiveInfoById ({ query = {}, params = {}, data = {} } = {}) {
    return apiClient.get(`/api/multi_class_inspection_live_info/${query.id}`, {  params })
  },
}