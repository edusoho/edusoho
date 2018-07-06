import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  selectedPlanIndex: 0,
  joinStatus: false,
  sourceType: 'img', //
  details: []
};

const isJoinedCourse = 'member.member_exist';

const mutations = {
  [types.SET_PLAN_INDEX](currentState, payload) {
    currentState.selectedPlanIndex = payload;
  },
  [types.GET_COURSE_DETAIL](currentState, payload) {
    currentState.details = payload;
  },
  [types.JOIN_COURSE](currentState, payload) {
    currentState.joinStatus = true;
    console.log(payload);
  },
  [types.SET_SOURCETYPE](currentState, payload) {
    console.log(payload);
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
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations
};
