import * as types from '@admin/store/mutation-types';
import Api from '@admin/api';
import treeEndTool from '@/utils/tree-end-tool';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

export const getCategories = ({ commit }) => Api.getCategories({
  query: {
    groupCode: 'course'
  }
}).then((res) => {
  const formatedRes = treeEndTool(res, 'children')
  commit(types.GET_CATEGORIES, formatedRes);
  return formatedRes;
})


export const getDraft = ({ commit }, { portal, type, mode }) => Api.getDraft({
  query: {
    portal,
    type,
  },
  params: {
    mode,
  }
});

export const saveDraft = ({ commit }, { portal, type, mode, data }) => Api.saveDraft({
  params: {
    type,
    mode,
  },
  query: { portal },
  data,
});

export const getCourseList = ({ commit }, params) => Api.getCourseList({
  params
});

export const getQrcode = ({ commit }, { route, preview, times, duration }) => Api.getQrcode({
  query: {
    route,
  },
  params: {
    preview,
    times,
    duration,
  },
});
