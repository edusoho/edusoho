import store from "@/store";
import i18n from "@/lang";
import whiteList from "@/router/config/white-list";
import Api from "@/api";
import {GetUrlParam} from "@/utils/utils";
import * as types from "@/store/mutation-types";

export const handleLocale = (locale) => {
  const language = locale.toLowerCase().replace('_', '-');

  store.state.language = language;
  i18n.locale = language;
}

export const handleUgc = (ugc) => {
  store.state.goods.show_review = ugc.review.enable;
  store.state.goods.show_course_review = ugc.review.course_enable;
  store.state.goods.show_classroom_review = ugc.review.classroom_enable;
  store.state.goods.show_question_bank_review = ugc.review.question_bank_enable;

  store.commit('SET_SETTING_UGC', ugc)
}

export const handleStorage = (storage) => {
  store.commit(types.GET_SETTINGS, { key: 'storageSetting', setting: storage })
}

export const handleVip = (vip) => {
  store.commit(types.GET_SETTINGS, { key: 'vipSettings', setting: vip })

  if (vip && vip.h5Enabled && vip.enabled) {
    return store.dispatch('setVipSwitch', true);
  }
}

export const handleSite = (site) => {
  document.title = site.name;
  store.commit(types.GET_SETTINGS, { key: 'settings', setting: site })

  if (!site.analytics || /document.write/.test(site.analytics)) return;

  let funStr = site.analytics.replace(/<\/?script[^>]*?>/gi, '');
  funStr = funStr.replace(/<noscript[^>]*?>.*?<\/noscript>/gis, '');

  const script = document.createElement('script');
  const scriptEle = document.getElementsByTagName('script')[0];

  script.type = 'text/javascript';
  script.innerHTML = funStr;
  scriptEle.parentNode.insertBefore(script, scriptEle);
}

export const handleCourse = (course) => {
  store.commit(types.GET_SETTINGS, { key: 'courseSettings', setting: course })
}

export const handleGoods = (goods) => {
  store.commit(types.GET_SETTINGS, { key: 'goodsSettings', setting: goods })
}

export const handleWap = (wap) => {
  const hashStr = window.location.hash;
  const getPathNameByHash = hash => {
    const hasQuery = hash.indexOf('?');

    if (hasQuery === -1) return hash.slice(1);

    return hash.match(/#.*\?/g)[0].slice(1, -1);
  };

  const isWhiteList = whiteList.includes(getPathNameByHash(hashStr));
  const hashParamArray = getPathNameByHash(hashStr).split('/');
  const hashHasToken = hashParamArray.includes('loginToken');
  const courseId = hashParamArray[hashParamArray.indexOf('course') + 1];

  if (hashHasToken) {
    const tokenIndex = hashParamArray.indexOf('loginToken');
    const tokenFromUrl = hashParamArray[tokenIndex + 1];

    store.state.token = tokenFromUrl;
    localStorage.setItem('token', tokenFromUrl);

    if (courseId) {
      window.location.href = `${location.origin}/h5/index.html#/course/${courseId}?backUrl=%2F`;
    }
  }

  const hasToken = window.localStorage.getItem('token');

  if (hasToken && !store.state.user) {
    Api.getUserInfo({}).then(res => {
      store.state.user = res;
      localStorage.setItem('user', JSON.stringify(res));
    });
  }

  if (!hasToken && Number(GetUrlParam('needLogin'))) {
    window.location.href = `${
      location.origin
    }/h5/index.html#/login?redirect=/course/${courseId}&skipUrl=%2F&account=${GetUrlParam(
      'account',
    )}`;
  }

  // 已登录状态直接跳转详情页
  if (hasToken && Number(GetUrlParam('needLogin'))) {
    window.location.href = `${location.href}&backUrl=%2F`;
  }

  if (!isWhiteList) {
    if (parseInt(wap.version, 10) !== 2) {
      // 如果没有开通微网校，则跳回老版本网校 TODO
      window.location.href = location.origin + getPathNameByHash(hashStr);

      return Promise.reject(false);
    }
  }

  return Promise.resolve(true);
}
