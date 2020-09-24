import * as types from '../mutation-types';

const state = {
  searchClassRoomList: {
    selectedData: {},
    courseList: [],
    paging: {},
    currentJoin: false, // 课程加入后是否采集用户信息
  },
};

const mutations = {
  [types.SET_CLASSROOMLIST](currentState, data) {
    currentState.searchClassRoomList = data;
  },
  [types.SET_CURRENT_CLASS_JOIN](currentState, payload) {
    currentState.currentJoin = payload;
  },
};

const actions = {
  setClassRoomList({ commit }, data) {
    commit(types.SET_CLASSROOMLIST, data);
    commit(types.SET_CURRENT_CLASS_JOIN, true);
  },
};

export default {
  namespaced: true,
  state,
  actions,
  mutations,
};
