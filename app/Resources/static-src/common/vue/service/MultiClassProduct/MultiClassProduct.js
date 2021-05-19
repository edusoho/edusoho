import { apiClient } from 'common/vue/service/api-client.js';
import BaseService from '../BaseService'

const baseUrl = '/api/multi_class_product';
const baseService = new BaseService({ baseUrl })

export const MultiClassProduct = {
  ...baseService
}