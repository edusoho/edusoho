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


export const getDraft = ({ commit }, { portal, type, mode }) => {
  return Api.getDraft({
    query: {
      portal,
      type,
    },
    params: {
      mode
    }
  })
}

export const saveDraft = ({ commit }, { portal, type, mode, data }) => {
  return Api.saveDraft({
    params: {
      type,
      mode,
    },
    query: { portal },
    data,
  })
}

export const getCourseList = ({ commit }, params) => {
  return Api.getCourseList({
    params,
  })
}
