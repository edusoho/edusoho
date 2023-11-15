import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/item_bank_exercises';

export const ItemBankExercises = {
  async search({ params }) {
    return apiClient.get(`${baseUrl}`, { params });
  },
  async getExercise(exerciseId) {
    return apiClient.get(`/api/item_bank_exercises/${exerciseId}`);
  }
}
