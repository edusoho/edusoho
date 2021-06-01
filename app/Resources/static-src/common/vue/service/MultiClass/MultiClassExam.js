import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'


export const MultiClassExam = _.assignIn({
  async getExamResults(params) {
    const url = `/api/multi_class/${params.multiClassId}/task/${params.taskId}/exam_result`;

    Reflect.deleteProperty(params, 'multiClassId')
    Reflect.deleteProperty(params, 'taskId')

    return apiClient.get(url, { params })
  },
  async getExams(params) {
    const url = `/api/multi_class/${params.multiClassId}/exams`;

    Reflect.deleteProperty(params, 'multiClassId')

    return apiClient.get(url, { params })
  }
})
