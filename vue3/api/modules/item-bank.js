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
}