import * as types from '../mutation-types';

const state = {
  searchClassRoomList: {
    selectedData: {},
    courseList: [],
    paging: {}
  }
};

const mutations = {
  [types.SET_CLASSROOMLIST](currentState, data) {
    currentState.searchClassRoomList = data;
  }
};

const actions = {
  setClassRoomList({ commit }, data) {
    commit(types.SET_CLASSROOMLIST, data);
  }
};

export default {
  namespaced: true,
  state,
  actions,
  mutations
};
