import { apiClient } from 'common/vue/service/api-client.js';
const baseUrl = '/api/live_statistic';

export const LiveStatistic = {
  // 课程直播统计页 - 直播列表
  async get({ params }) {
    return apiClient.get(`${baseUrl}`, { params });
  },

  // 课程直播统计页 - 直播列表 - 详情页头部直播数据
  async getLiveDetails({ query }) {
    return apiClient.get(`${baseUrl}/${query.taskId}/detail`);
  },

  // 课程直播统计页 - 直播列表 - 详情页成员数据
  async getLiveMembers({ query, params }) {
    return apiClient.get(`${baseUrl}/${query.taskId}/members`, { params });
  },

  // 课程直播统计页 - 直播列表 - 点名统计
  async getLiveRollCall({ query, params }) {
    return apiClient.get(`${baseUrl}/${query.taskId}/roll_call`, { params });
  },

  // 班级直播统计 - 列表页
  async getClassroom({ query, params }) {
    return apiClient.get(`${baseUrl}/${query.classroomId}/classroom_live`, { params });
  }
};
