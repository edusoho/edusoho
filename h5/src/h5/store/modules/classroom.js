import * as types from '../mutation-types';

const state = {
  searchClassRoomList: {
    selectedData: {},
    courseList: [],
    paging: {},
    currentJoin: false, // 班级加入后是否采集用户信息
  },
};

const mutations = {
  [types.SET_CLASSROOMLIST](currentState, data) {
    currentState.searchClassRoomList = data;
  },
  [types.SET_CURRENT_JOIN_CLASS](currentState, payload) {
    currentState.currentJoin = payload;
  },
};

const actions = {
  setClassRoomList({ commit }, data) {
    commit(types.SET_CLASSROOMLIST, data);
    commit(types.SET_CURRENT_JOIN_CLASS, true);
  },
};

export default {
  namespaced: true,
  state,
  actions,
  mutations,
};
