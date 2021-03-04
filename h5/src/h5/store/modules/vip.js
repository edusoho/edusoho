import Api from '@/api';

const state = {
  vipLevels: [], // 会员等级列表
  vipTree: {},
};

const mutations = {
  SET_VIP_LEVELS: (state, vipLevels) => {
    state.vipLevels = vipLevels;
  },
  SET_VIP_TREE: (state, vipTree) => {
    state.vipTree = vipTree;
  },
};

const actions = {
  // get vip levels
  getVipLevels({ commit }) {
    Api.getVipLevels().then(res => {
      commit('SET_VIP_LEVELS', res);
      const tempArr = {
        data: [{ name: '全部', type: 'all' }],
        moduleType: 'vip',
        text: '会员等级',
        type: 'vip',
      };
      res.forEach(item => {
        tempArr.data.push({
          name: item.name,
          type: item.id,
        });
      });
      commit('SET_VIP_TREE', tempArr);
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
