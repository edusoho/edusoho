import { apiClient } from 'common/vue/service/api-client.js';
import _ from 'lodash';
import BaseService from '../BaseService'
const baseUrl = '/api/me';

const baseService = new BaseService({ baseUrl })

export const Me = _.assignIn(baseService, {
  // 错题列表
  async getWrongBooks() {
    return apiClient.get(`${this.baseUrl}/wrong_books`)
  },

  // 我的错题题目分类
  async getWrongBooksCertainTypes(params) {
    return apiClient.get(`${this.baseUrl}/wrong_books/${params.targetType}/certain_types`, { params })
  },

  // 我的课程里搜索课程
  async searchCourses(params) {
    return apiClient.get(`${this.baseUrl}/courses`, { params })
  },

  // 我的课程里搜索课程
  async searchFavoriteCourses(params) {
    return apiClient.get(`${this.baseUrl}/favorite_course_sets`, { params })
  },

  // 我的班级里搜索班级
  async searchClassrooms(params) {
    return apiClient.get(`${this.baseUrl}/classrooms`, { params })
  }
});
