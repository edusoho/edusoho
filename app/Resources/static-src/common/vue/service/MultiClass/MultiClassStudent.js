import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassStudent = _.assignIn({
  async search(params) {
    return apiClient.get(`/api/multi_class/${params.id}/students`, { params })
  },
  async add(params) {
    return apiClient.post(`/api/multi_class/${params.id}/students`, params)
  },
  async deleteMultiClassMember(multiClassId, userId) {
    return apiClient.delete(`/api/multi_class/${multiClassId}/students/${userId}`)
  },

  async batchDeleteClassMember(multiClassId, data) {
    return apiClient.post(`/api/multi_class/${multiClassId}/student_batch_delete`, data);
  },
  async getGroup(multiClassId) {
    return apiClient.get(`/api/multi_class/${multiClassId}/groups`);
  },
  async editGroup(multiClassId, groupId, params) {
    return apiClient.patch(`/api/multi_class/${multiClassId}/student_groups/${groupId}`, params);
  },
})
