import Api from '@/api';
import * as types from '../mutation-types';

const state = {
  ItemBankExercise: {},
  ItemBankModules: {},
  searchItemBankList: {
    selectedData: {},
    courseList: [],
    paging: {},
  },
};

const mutations = {
  [types.SET_ITEM_BANK_EXERCISE](currentState, data) {
    currentState.ItemBankExercise = data;
  },
  [types.SET_ITEM_BANK_MODULES](currentState, data) {
    currentState.ItemBankModules = data;
  },
  [types.SET_ITEMBANKLIST](currentState, data) {
    currentState.searchItemBankList = data || {};
  },
};

const actions = {
  setItemBankExercise({ commit }, id) {
    const query = { id };
    Api.getItemBankExercise({ query })
      .then(res => {
        commit(types.SET_ITEM_BANK_EXERCISE, res);
      })
      .catch(err => {
        console.log(err);
      });
  },
  getDirectoryModules({ commit }, id) {
    const query = { id };
    return new Promise((resolve, reject) => {
      Api.getItemBankModules({ query })
        .then(res => {
          resolve(res);
          commit(types.SET_ITEM_BANK_MODULES, res);
        })
        .catch(err => {
          reject(err);
          console.log(err);
        });
    });
  },
  setItemBankList({ commit }, data) {
    commit(types.SET_ITEMBANKLIST, data);
  },
};

export default {
  namespaced: true,
  state,
  actions,
  mutations,
};
