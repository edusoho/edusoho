import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassStudentExam = _.assignIn({
  async searchStudentExamResults(multiClassId, studentId, params) {
    const url = `/api/multi_class/${multiClassId}/student/${studentId}/exam_results`;
    return apiClient.get(url, { params })
  },
})
