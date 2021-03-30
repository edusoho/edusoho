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
    return new Promise((resolve, reject) => {
      Api.getVipLevels()
        .then(res => {
          resolve(res);
          commit('SET_VIP_LEVELS', res);
        })
        .catch(err => {
          reject(err);
        });
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
