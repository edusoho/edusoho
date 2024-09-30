import defaultLang from './lang/zh-CN';
import enLang from './lang/en-US';
import Vue from 'vue';
import Format from './format';
let lang = defaultLang;
if (app.lang === 'en') {
  lang = enLang;
}

const format = Format(Vue);
let i18nHandler = function() {
  const vuei18n = Object.getPrototypeOf(this).$t;
  if (typeof vuei18n === 'function') {
    return vuei18n.apply(this, arguments);
  }
};

export const t = function(path, options) {
  let value = i18nHandler.apply(this, arguments);
  if (value !== null && typeof value !== 'undefined') {
    return value;
  }
  const array = path.split('.');
  let current = lang;

  for (let i = 0, j = array.length; i < j; i++) {
    const property = array[i];
    value = current[property];
    if (i === j - 1) return format(value, options);
    if (!value) return '';
    current = value;
  }
  return '';
};

export const use = function(l) {
  lang = l || lang;
};

export const i18n = function(fn) {
  i18nHandler = fn || i18nHandler;
};

export default { use, t, i18n };
