import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'

const baseUrl = '/api/teacher';
const baseService = new BaseService({ baseUrl })

export const Teacher = _.assignIn(baseService, {
  // 教师取消推荐
  async cancelPromotion(id) {
    return apiClient.delete(`${baseUrl}/${id}/promotion`)
  },

  // 教师取消推荐
  async promotion(id, params) {
    return apiClient.post(`${baseUrl}/${id}/promotion`, params)
  }
})
