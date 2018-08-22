import * as types from '@admin/store/mutation-types';
import Api from '@admin/api';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

// 全局设置
export const getGlobalSettings = ({ commit }, { type }) =>
  new Promise((resolve, reject) => {
    Api.getSettings({
      query: {
        type
      }
    }).then(res => {
      document.title = res.name;
      commit(types.GET_SETTINGS, res);
      resolve(res);
      return res;
    }).catch(err => reject(err));
  });
