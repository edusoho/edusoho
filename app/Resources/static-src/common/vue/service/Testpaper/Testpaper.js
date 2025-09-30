import {apiClient} from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService';

const baseUrl = '/api/assessment';
const baseService = new BaseService({baseUrl});

export const Testpaper = _.assignIn(baseService, {
  async search(params) {
    return apiClient.get(`/api/assessment`, {params});
  },
  async changeStatus(id, status) {
    return apiClient.post(`/api/assessment/${id}/status`, {status});
  },
  async create(params) {
    return await apiClient.post(`/api/assessment`, params);
  },
  async delete(params) {
    return await apiClient.delete(`/api/assessment`, {data: params});
  },
  async get(id) {
    return await apiClient.get(`/api/assessment/${id}`);
  },
  async searchExercise(params) {
    return apiClient.get(`/api/assessmentExercise`, {params});
  },
  async addToExercise(params) {
    return await apiClient.post(`/api/assessmentExercise`, params);
  },
  async deleteExercise(params) {
    return await apiClient.delete(`/api/assessmentExercise`, {data: params});
  },
  async regenerate(id) {
    return await apiClient.post(`/api/assessment/${id}/regenerate`);
  }
});
