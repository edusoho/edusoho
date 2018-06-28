import * as types from '../mutation-types';

const state = {
  selectedPlanIndex: 0
};

const mutations = {
  [types.SET_PLAN_INDEX](state, payload) {
    state.selectedPlanIndex = payload;
  }
};

export default {
  namespaced: true,
  state,
  mutations
};
