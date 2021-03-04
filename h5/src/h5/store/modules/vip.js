import Api from '@/api';

const state = {
  vipLevels: [], // 会员等级列表
};

const mutations = {
  SET_VIP_LEVELS: (state, vipLevels) => {
    state.vipLevels = vipLevels;
  },
};

const actions = {
  // get vip levels
  getVipLevels({ commit }) {
    Api.getVipLevels().then(res => {
      commit('SET_VIP_LEVELS', res);
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
