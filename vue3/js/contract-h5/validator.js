import { t } from './vue-lang'

export const IDNumberValidator = (rule, value, callback) => {
  const reg = new RegExp('^(1[1-5]|2[1-3]|3[1-7]|4[1-6]|5[0-4]|6[1-5]|7[1-8]|8[1-3])\d{4}((19|20)\d{2})(0[1-9]|1[0-2])((0[1-9]|[12]\d|3[01]))\d{3}[\dXx]$')

  const result = reg.exec(value)

  if (!result) {
    callback(new Error(t('validator.IDNumber')))
  }

  callback()
}

export const phoneNumberValidator = (rule, value, callback) => {
  const reg = new RegExp('^(13[0-9]|14[5-9]|15[0-3,5-9]|16[2,5,6,7]|17[0-8]|18[0-9]|19[0-3,5-9])\d{7}$')

  const result = reg.exec(value)

  if (!result) {
    callback(new Error(t('validator.phoneNumber')))
  }

  callback()
}