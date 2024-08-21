import { createI18n } from 'vue-i18n'
import { IDNumberValidator } from './validator'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
	globalInjection: true,
  messages: {
    zh_CN: {
      title: '签署合同',
      contract_detail: '查看合同详情',
      placeholder: {
        name: '请输入姓名',
        IDNumber: '请输入身份证号',
        phoneNumber: '请输入手机号'
      },
      validator: {
        IDNumber: '身份证号不符合格式',
        phoneNumber: '手机号不符合格式'
      },
      handwritten: '手写签名',
      confirmSign: '确认签署',
    },
    en: {
      title: 'Sign Contract',
      contract_detail: 'View Contract details',
      placeholder: {
        name: 'Please enter your name',
        IDNumber: 'Please enter your ID number',
        phoneNumber: 'Please enter your phone number'
      },
      validator: {
        IDNumber: 'The ID number does not match the format',
        phoneNumber: 'The phone number does not match the format'
      },
      handwritten: 'Handwritten Signature',
      confirmSign: 'Confirm Sign',
    },
  },
})

export const t = i18n.global.t

export default i18n