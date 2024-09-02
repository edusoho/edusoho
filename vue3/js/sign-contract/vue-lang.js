import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      btn: {
        cancel: '取消',
        goToSign: '去签署',
        clear: '清空',
        submit: '提交',
        close: '关闭',
        confirmationSignature: '确认签署',
        return: '返回',
      },
      modal: {
        title: {
          electronicContractSigning: '电子合同签署'
        },
        SignAnElectronicContract: '签署电子合同',
        confirmContentPart01: '开始学习前请签署',
        confirmContentPart02: '，以确保正常享受后续服务',
        signContract: '签署合同',
        viewContractDetails: '查看合同详情',
      },
      label: {
        partyBName: '乙方姓名',
        partyBiDNumber: '乙方身份证号',
        contactInformationOfPartyB: '乙方联系方式',
        handwrittenSignature: '手写签名',
        startSigning: '开始签名',
        reSign: '重新签名',
        contractParticular: '合同详情',
      },
      pagination: {
        total: '共',
        item: '项'
      },
      placeholder: {
        pleaseEnter: '请输入',
      },
      validation: {
        enterName: '请输入乙方姓名',
        enterNumberOrChineseCharacters: '只能输入汉字和英文',
        enterIDNumber: '请输入乙方身份证号',
        IDNumberFormat: '身份证号不符合格式',
        enterContactInformation: '请输入乙方联系方式',
        enterNumber: '请填写数字',
        enterHandwrittenSignature: '请输入手写签名',
      },
      tip: {
        clickOnThisArea: '请点击此区域进行手写签名',
        makeSure: '请确保“字迹清晰”并尽量把“签字范围”撑满',
      },
      message: {
        submitSuccessfully: '提交成功',
        enterHandwrittenSignature: '请输入手写签名',
        signedSuccessfully: '签署成功',
      }
    },




    en: {
      btn: {
        cancel: 'Cancel',
        goToSign: 'Go To Sign',
        clear: 'Clear',
        submit: 'Submit',
        close: 'Close',
        confirmationSignature: 'Confirmation Signature',
        return: 'Return',
      },
      modal: {
        title: {
          electronicContractSigning: 'Contract signing'
        },
        SignAnElectronicContract: 'Sign an contract',
        confirmContentPart01: 'Please sign the ',
        confirmContentPart02: ' before starting the study to ensure normal access to subsequent services',
        signContract: 'Sign a contract',
        viewContractDetails: 'View contract details',
      },
      label: {
        partyBName: 'Name',
        partyBiDNumber: 'ID number',
        contactInformationOfPartyB: 'Contact way',
        handwrittenSignature: 'Signature',
        startSigning: 'Start signing',
        reSign: 'Re-sign',
        contractParticular: 'Contract particular',
      },
      pagination: {
        total: 'In total',
        item: 'item'
      },
      placeholder: {
        pleaseEnter: 'Please enter',
      },
      validation: {
        enterName: 'Please enter Party B\'s name',
        enterNumberOrChineseCharacters: 'Only Chinese characters and English can be entered',
        enterIDNumber: 'Please enter Party B\'s ID number',
        IDNumberFormat: 'The ID number does not match the format',
        enterContactInformation: 'Please enter Party B\'s contact information',
        enterNumber: 'Please fill in the number',
        enterHandwrittenSignature: 'Please enter your handwritten signature',
      },
      tip: {
        clickOnThisArea: 'Please click on this area to sign by hand',
        makeSure: 'Please make sure that the "handwriting is legible" and try to fill the "signature area"',
      },
      message: {
        submitSuccessfully: 'Submit successfully',
        enterHandwrittenSignature: 'Please enter your handwritten signature',
        signedSuccessfully: 'Signed successfully'
      }
    },
  },
})

export const t = i18n.global.t

export default i18n