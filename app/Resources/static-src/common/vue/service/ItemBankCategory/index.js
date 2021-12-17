import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/item_bank_category';

export const ItemBankCategory = {
  async get() {
    return apiClient.get(`${baseUrl}`);
  }
}