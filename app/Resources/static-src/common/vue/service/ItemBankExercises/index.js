import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/item_bank_exercises';

export const ItemBankExercises = {
  async search({ params }) {
    return apiClient.get(`${baseUrl}`, { params });
  }
}