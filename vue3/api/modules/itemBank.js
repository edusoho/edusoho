import {apiClient} from '../api-client';

export default {
  async getItemBankCategory() {
    return apiClient.get('/itemBankCategory');
  },
  async searchItemBank(params) {
    return apiClient.get('/itemBankExercise', {params});
  },
  async bindItemBankExercise(params) {
    return apiClient.post('/itemBankExerciseBind', params);
  },
  async getBindItemBankExercise(params) {
    return apiClient.get('/itemBankExerciseBind', {params});
  },
  async sequenceBindItemBankExercise(params) {
    return apiClient.post('/itemBankExerciseBindSeq', params);
  },
  async deleteBindItemBank(id) {
    return apiClient.delete(`/itemBankExerciseBind/${id}`);
  },
  async getMyBindItemBank(params) {
    return apiClient.get('/me/item_bank_exercises', {params});
  },
  async getRepeatQuestion(bank_id, categoryId = '') {
    return apiClient.get(`/api/question_bank/${bank_id}/duplicative_material?categoryId=${categoryId}`)
  },
}