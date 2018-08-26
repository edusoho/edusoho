import * as types from '@admin/store/mutation-types';
import Api from '@admin/api';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

export const getCategories = ({ commit }) => {
  return Api.getCategories({
    query: {
      groupCode: 'course'
    }
  }).then((res) => {
    commit(types.GET_CATEGORIES, res);
    return res;
  })
};

