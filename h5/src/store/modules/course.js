import Api from '@/api';
import * as types from '../mutation-types';
/* eslint no-shadow: [2, { "hoist": "never" }] */
const state = {
  single: {},
  courseSet: [],
  paging: {},
};

const mutations = {
  [types.GET_COURSE](state, payload) {
    state.single = payload;
  },
  [types.GET_COURSE_LIST](state, payload) {
    state.courseSet = payload.data;
    state.paging = payload.paging;
  },
};

const actions = {
  getCourse({ commit }, { courseId }) {
    commit(types.UPDATE_LOADING_STATUS, true, { root: true });
    return Api.getCourse({
      query: {
        courseId,
      },
    }).then(res => {
      commit(types.UPDATE_LOADING_STATUS, false, { root: true });
      commit(types.GET_COURSE, res);
      return res;
    });
  },
  getCourses({ commit }, { limit = 10, offset = 0, sort = 'createdTime' }) {
    commit(types.UPDATE_LOADING_STATUS, true, { root: true });
    return Api.getCourses({
      params: {
        limit,
        offset,
        sort,
      },
    }).then(res => {
      commit(types.UPDATE_LOADING_STATUS, false, { root: true });
      commit(types.GET_COURSE_LIST, res);
      return res;
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
