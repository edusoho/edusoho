import { apiClient } from 'common/vue/service/api-client.js';

export const Repeat = {
    // 获取课程教师
    async getRepeatQuestion(bank_id, query) {
      return apiClient.get(`/api/question_bank/${bank_id}/duplicative_material`, { query })
    }

  }
  