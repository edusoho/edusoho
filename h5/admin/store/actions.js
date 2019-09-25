import * as types from 'admin/store/mutation-types';
import Api from 'admin/api';
import treeDigger from 'admin/utils/tree-digger';
/* eslint-disable */
export const updateLoading = ({ commit }, { isLoading }) => {
  commit(types.UPDATE_LOADING_STATUS, { isLoading });
};

// 全局设置
export const getGlobalSettings = ({ commit }, { type, key }) => Api.getSettings({
  query: {
    type
  }
}).then(res => {
  commit(types.GET_SETTINGS, {
    key,
    setting: res || {}
  });
  return res;
});

// 全局vip元素显示开关
export const setVipLevels = ({ commit }) => Api.getVipLevels().then(levels => {
  const levelsExist = levels;
  commit(types.GET_SETTINGS, { key: 'vipLevels', setting: levelsExist });
  return levelsExist;
});

// vip插件安装
export const setVipSetupStatus = ({ commit }) => Api.vipPlugin().then(vipPlugin => {
  const pluginInfo = vipPlugin || {};
  const pluginStatus = Object.keys(vipPlugin).length > 1;
  commit(types.GET_SETTINGS, { key: 'vipSetupStatus', setting: pluginStatus });
  commit(types.GET_SETTINGS, { key: 'vipPlugin', setting: pluginInfo });
  return vipPlugin;
});

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

// 全局优惠券设置开关
export const getCouponSetting = ({ commit }, params) => Api.getCouponSetting.then((enabled) => {
  const couponEnabled = parseInt(enabled);
  commit(types.GET_SETTINGS, { key: 'couponSetting', setting: couponEnabled });
  return couponEnabled;
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
