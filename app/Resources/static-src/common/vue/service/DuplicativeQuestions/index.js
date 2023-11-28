import { apiClient } from 'common/vue/service/api-client.js';

export const Repeat = {
    // 获取查重题目数量
    async getRepeatQuestion(bank_id, query={}) {
      return apiClient.get(`/api/question_bank/${bank_id}/duplicative_material`, { query })
    },

    // 获取查重题目信息
    async getRepeatQuestionInfo(bank_id, params={}) {
        return apiClient.post(`/api/question_bank/${bank_id}/duplicative_material_item`, params)
    }

  }
  