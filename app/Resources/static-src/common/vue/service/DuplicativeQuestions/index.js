import { apiClient } from 'common/vue/service/api-client.js';

export const Repeat = {
    // 获取查重题目数量
    async getRepeatQuestion(bank_id, query={}) {
      return apiClient.get(`/api/question_bank/${bank_id}/duplicative_material`, { query })
    },

    // 获取查重题目信息
    async getRepeatQuestionInfo(bank_id, params={}) {
        return apiClient.post(`/api/question_bank/${bank_id}/duplicative_material_item`, params)
    },

    // 删除题目
    async delQuestion(bank_id, questionId) {
      return apiClient.post(`/question_bank/${bank_id}/question/${questionId}/delete`)
    },

    // 获取一道题目信息
    async getQuestionInfo(id) {
      return apiClient.get(`/api/item/${id}`)
    }
  }
  