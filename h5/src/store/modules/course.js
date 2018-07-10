import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  selectedPlanIndex: 0,
  joinStatus: false, // 当前计划是否已加入学习
  sourceType: 'img', //
  details: {}
};

const hasJoinedCourse = course => course.access.code === 'member.member_exist';

const mutations = {
  [types.SET_PLAN_INDEX](currentState, payload) {
    currentState.selectedPlanIndex = payload;
    currentState.joinStatus = hasJoinedCourse(payload[currentState.selectedPlanIndex]);
  },
  [types.GET_COURSE_DETAIL](currentState, payload) {
    currentState.details = payload;
    currentState.joinStatus = hasJoinedCourse(payload[currentState.selectedPlanIndex]);
  },
  [types.JOIN_COURSE](currentState, payload) {
    currentState.joinStatus = true;
    console.log('join-course', payload);
  },
  [types.SET_SOURCETYPE](currentState, payload) {
    currentState.sourceType = payload;
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
