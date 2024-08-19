import { createI18n } from 'vue-i18n'

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
        idCard: '请输入身份证号',
        phone: '请输入手机号'
      },
      handwritten: '手写签名',
      confirmSign: '确认签署',
    },
    en: {
      title: 'Sign Contract',
      contract_detail: 'View Contract details',
      placeholder: {
        name: 'Please enter your name',
        idCard: 'Please enter your ID number',
        phone: 'Please enter your phone number'
      },
      handwritten: 'Handwritten Signature',
      confirmSign: 'Confirm Sign',
    },
  },
})

export const t = i18n.global.t

export default i18n