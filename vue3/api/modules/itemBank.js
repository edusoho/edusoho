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
  async getItemBankExercises(itemBankId) {
    return apiClient.get(`/item_bank_exercises/${itemBankId}`, );
  },
  async setItemBankExerciseAgent(exerciseId, params) {
    return apiClient.post(`/item_bank_exercise/${exerciseId}/agent`, params);
  },
}