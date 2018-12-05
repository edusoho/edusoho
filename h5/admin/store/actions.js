import * as types from '@admin/store/mutation-types';
import Api from '@admin/api';
import treeDigger from '@admin/utils/tree-digger';

export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

// 课程分类
export const getCourseCategories = ({ commit }) => Api.getCategories({
  query: {
    groupCode: 'course'
  }
}).then(res => {
  res.unshift({ id: '0', children: undefined, name: '全部' });
  const formatedRes = treeDigger(res, children => {
    if (!children.length) {
      children = undefined;
    }
    return children;
  });
  commit(types.GET_COURSE_CATEGORIES, formatedRes);
  return formatedRes;
});

// 班级分类
export const getClassCategories = ({ commit }) => Api.getCategories({
  query: {
    groupCode: 'classroom'
  }
}).then(res => {
  res.unshift({ id: '0', children: undefined, name: '全部' });
  const formatedRes = treeDigger(res, children => {
    if (!children.length) {
      children = undefined;
    }
    return children;
  });
  commit(types.GET_CLASS_CATEGORIES, formatedRes);
  return formatedRes;
});


// 获取后台配置（草稿／正式）
export const getDraft = ({ commit }, { portal, type, mode }) => Api.getDraft({
  query: {
    portal,
    type
  },
  params: {
    mode
  }
});

// 删除后台配置（草稿／正式）
export const deleteDraft = ({ commit }, { portal, type, mode }) => Api.deleteDraft({
  query: {
    portal,
    type
  },
  params: {
    mode
  }
});

// 保存后台配置（草稿／正式）
export const saveDraft = ({ commit }, { portal, type, mode, data }) => Api.saveDraft({
  params: {
    type,
    mode
  },
  query: { portal },
  data
});

// 课程搜索列表数据
export const getCourseList = ({ commit }, params) => Api.getCourseList({
  params
});

export const getClassList = ({ commit }, params) => Api.getClassList({
  params
});

// 营销活动搜索列表数据
export const getMarketingList = ({ commit }, params) => Api.getMarketingList({
  params
});

// 优惠券搜索列表数据
export const getCouponList = ({ commit }, params) => Api.getCouponList({
  params
});

// 后台配置预览二维码
export const getQrcode = ({ commit }, { route, preview, times, duration }) => Api.getQrcode({
  query: {
    route
  },
  params: {
    preview,
    times,
    duration
  }
});
