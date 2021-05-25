import Api from '@/api';

const state = {
  vipLevels: [], // 会员等级列表
  vipOpenStatus: null, // 会员插件是否开启
  upgradeMode: '',
};

const mutations = {
  SET_VIP_LEVELS: (state, vipLevels) => {
    state.vipLevels = vipLevels;
  },

  SET_VIP_OPEN_STATUS: (state, status) => {
    state.vipOpenStatus = status;
  },

  SET_VIP_UPGRADE_MODE: (state, status) => {
    state.upgradeMode = status;
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

  getVipOpenStatus({ commit }) {
    return new Promise((resolve, reject) => {
      Api.getVipOpenStatus()
        .then(res => {
          resolve(res);
          commit('SET_VIP_OPEN_STATUS', res.h5Enabled);
          commit('SET_VIP_UPGRADE_MODE', res.upgradeMode);
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
