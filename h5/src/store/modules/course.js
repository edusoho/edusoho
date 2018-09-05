import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  selectedPlanId: 0,
  joinStatus: false, // 当前计划是否已加入学习
  sourceType: 'img', //
  details: {},
  taskId: 0 // 任务id
};

const hasJoinedCourse = course => course.member;

const mutations = {
  [types.GET_COURSE_DETAIL](currentState, payload) {
    currentState.selectedPlanId = payload.id;
    currentState.details = payload;
    currentState.joinStatus = hasJoinedCourse(payload);
    currentState.sourceType = 'img';
  },
  [types.JOIN_COURSE](currentState) {
    currentState.joinStatus = true;
  },
  [types.SET_SOURCETYPE](currentState, payload) {
    currentState.sourceType = payload.sourceType;
    currentState.taskId = payload.taskId;
  }
};

const actions = {
  getCourseDetail({ commit }, { courseId }) {
    return Api.getCourseDetail({
      query: {
        courseId
      }
    }).then(res => {
      commit(types.GET_COURSE_DETAIL, res);
      return res;
    });
  },
  joinCourse({ commit }, { id }) {
    return Api.joinCourse({
      query: {
        id
      }
    }).then(res => {
      commit(types.JOIN_COURSE, res);
      return res;
    });
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations
};
