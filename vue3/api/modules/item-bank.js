import {apiClient} from '../api-client';

export default {
  async getItemBankCategory() {
    return apiClient.get('/itemBankCategory');
  },
  async search(params) {
    return apiClient.get('/itemBankExercise', {params});
  },
  async bindItemBankExercise(params) {
    return apiClient.post('/itemBankExerciseBind', params);
  },
  async getBindItemBankExercise(params) {
    return apiClient.get('/itemBankExerciseBind', {params});
  },
}