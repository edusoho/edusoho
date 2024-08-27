import i18n from '@/lang';

export const truenameValidator = () => {
  const rule1 = {
    validator: (value) => !!value,
    message: i18n.t('contract.placeholder.truename')
  }

  const rule2 = {
    validator: (value) => /^[\u4e00-\u9fa5]+$/.test(value) || /^[A-Za-z\s]+$/.test(value),
    message: i18n.t('contract.validator.truename')
  }

  return [rule1, rule2]
}

export const IDNumberValidator = () => {
  const rule1 = {
    validator: (value) => !!value,
    message: i18n.t('contract.placeholder.IDNumber')
  }

  const rule2 = {
    validator: (value) => {
      const reg = /^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[\dXx]$/;

      return !!reg.exec(value)
    },
    message: i18n.t('contract.validator.IDNumber')
  }

  return [rule1, rule2]
}

export const phoneNumberValidator = () => {
  const rule1 = {
    validator: (value) => !!value,
    message: i18n.t('contract.placeholder.phoneNumber')
  }

  const rule2 = {
    validator: (value) => {
      const reg = /\d{11}$/

      return !!reg.exec(value)
    },
    message: i18n.t('contract.validator.phoneNumber')
  }

  return [rule1, rule2]
}

export const handSignatureValidator = () => {
  const rule1 = {
    validator: (value) => !!value,
    message: i18n.t('validator.handSignature')
  }

  return [rule1]
}
