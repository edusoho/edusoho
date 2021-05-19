import { apiClient } from 'common/vue/service/api-client.js';
import BaseService from '../BaseService'

const baseUrl = '/api/assistants';
const baseService = new BaseService({ baseUrl })

export const Assistant = {
  ...baseService
}