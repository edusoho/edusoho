import {apiClient} from '../api-client';

export default {
  async getRepeatQuestion(bank_id, categoryId = '') {
    return apiClient.get(`/api/question_bank/${bank_id}/duplicative_material?categoryId=${categoryId}`)
  },
}