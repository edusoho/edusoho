import { t } from './vue-lang'

export const truenameValidator = (rule, value, callback) => {
  if (!value) {
    return Promise.reject(new Error(t('placeholder.truename')))
  }

  if (/^[\u4e00-\u9fa5]+$/.test(value) === false && /^[A-Za-z\s]+$/.test(value) === false) {
    return Promise.reject(new Error(t('validator.truename')))
  }

  return Promise.resolve()
}

export const IDNumberValidator = (rule, value, callback) => {
  if (!value) {
    return Promise.reject(new Error(t('placeholder.IDNumber')))
  }

  const reg = /^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[\dXx]$/;  
  
  const result = reg.exec(value)

  if (!result) {
    return Promise.reject(new Error(t('validator.IDNumber')))
  }

  return Promise.resolve()
}

export const phoneNumberValidator = (rule, value, callback) => {
  if (!value) {
    return Promise.reject(new Error(t('placeholder.phoneNumber')))
  }

  const reg = new RegExp('^(13[0-9]|14[5-9]|15[0-3,5-9]|16[2,5,6,7]|17[0-8]|18[0-9]|19[0-3,5-9])\d{7}$')

  const result = reg.exec(value)

  if (!result) {
    return Promise.reject(new Error(t('validator.phoneNumber')))
  }

  return Promise.resolve()
}

export const handSignatureValidator = (rule, value, callback) => {
  if (!value) {
    return Promise.reject(new Error(t('validator.handSignature')))
  }

  return Promise.resolve()
}