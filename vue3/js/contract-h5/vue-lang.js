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
        truename: '请输入姓名',
        IDNumber: '请输入身份证号',
        phoneNumber: '请输入手机号'
      },
      validator: {
        truename: '请输入纯汉字或纯英文',
        IDNumber: '身份证号不符合格式',
        phoneNumber: '手机号不符合格式',
        handSignature: '请完成手写签名',
      },
      handwritten: '手写签名',
      confirmSign: '确认签署',
      signSuccess: '签署成功',
      reSign: '重新签名',
      signing: '签署中...',
      contractDetail: '合同详情',

      submit: '提交',
      clear: '清空',
      acrossScreen: '请横着屏幕手写',
      signScope: '签字范围',
      signTips: '请确保“字迹清晰”并尽量把“签字范围”撑满',
      acrossTips: '为了更好的视觉体验，请在关闭手机的旋转功能后再进行操作',
    },
    en: {
      title: 'Sign Contract',
      contract_detail: 'View Contract details',
      placeholder: {
        truename: 'Please enter your name',
        IDNumber: 'Please enter your ID number',
        phoneNumber: 'Please enter your phone number'
      },
      validator: {
        truename: 'Please enter pure Chinese or English',
        IDNumber: 'The ID number does not match the format',
        phoneNumber: 'The phone number does not match the format',
        handSignature: 'Please sign by your hand',
      },
      handwritten: 'Handwritten Signature',
      confirmSign: 'Confirm Sign',
      signSuccess: 'Signed successfully',
      reSign: 'Sign again',
      signing: 'Signing',
      contractDetail: 'Contract Details',

      submit: 'Submit',
      clear: 'Clear',
      acrossScreen: 'Please write by hand across the screen',
      signScope: "Signature's Scope",
      signTips: 'Please make sure that the "handwriting is legible" and try to fill the "signature area"',
      acrossTips: 'For better visual experience, please turn off the rotation function of the phone before operating.',
    },
  },
})

export const t = i18n.global.t

export default i18n