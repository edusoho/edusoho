import { apiClient } from 'common/vue/service/api-client.js';
import BaseService from '../BaseService'

const baseUrl = '/api/teachers';
const baseService = new BaseService({ baseUrl })

export const Teachers = {
  ...baseService
}