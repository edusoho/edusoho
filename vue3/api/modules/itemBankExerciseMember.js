import {apiClient, ajaxClient} from '../api-client';

export default {
  async search(exerciseId, params) {
    return apiClient.get(`/item_bank_exercise/${exerciseId}/members`, {params});
  },
  async remove(exerciseId, userId) {
    return ajaxClient.post(`/item_bank_exercise/${exerciseId}/manage/student/${userId}/remove`);
  },
  async batchRemove(exerciseId, studentIds) {
    return ajaxClient.post(`/item_bank_exercise/${exerciseId}/manage/students/remove`, new URLSearchParams({studentIds}));
  },
};