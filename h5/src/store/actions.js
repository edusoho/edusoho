import * as types from '@/store/mutation-types';
import Api from '@/api';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

export const userLogin = ({ commit }, { username, password }) =>
  Api.login({
    headers: {
      Authorization: `Basic ${btoa(unescape(encodeURIComponent(`${username}:${password}`)))}`
    }
  }).then(res => {
    commit(types.USER_LOGIN, res);
    return res;
  });
