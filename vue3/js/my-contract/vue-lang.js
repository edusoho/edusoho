import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      title: '我的合同',
      associatedCurriculum: '关联课程',
      relatedQuestionBank: '关联题库',
      associatedClass: '关联班级',
      btn: {
        view: '查看',
        close: '关闭',
      },
      message: {
        noContract: '暂无合同',
      },
      modal: {
        contractNumber: '合同编号',
        contractSigning: '电子合同签署',
        partyA: '甲方',
        signingDate: '签约日期',
        partyB: '乙方',
        handSignature: '手写签名',
        partyBName: '乙方姓名',
        iDNumber: '身份证号',
        contactInformation: '联系方式',
        dataWillDisappear: '离开后已编辑的数据将消失...',
        footer: {
          btn: {

          }
        }
      },
      pagination: {
        total: '共',
        item: '项'
      },
    },



    en: {
      title: 'My contract',
      associatedCurriculum: 'Associated curriculum',
      relatedQuestionBank: 'Related question bank',
      associatedClass: 'Associated class',
      btn: {
        view: 'View',
        close: 'Close',
      },
      message: {
        noContract: 'No contract',
      },
      modal: {
        contractNumber: 'Contract number',
        contractSigning: 'Contract signing',
        partyA: 'Party A',
        signingDate: 'Signing date',
        partyB: 'Party B',
        handSignature: 'Hand signature',
        partyBName: 'Party B\'s name',
        iDNumber: 'ID number',
        contactInformation: 'Contact information',
        dataWillDisappear: 'The edited data will disappear when you leave...',
        footer: {
          btn: {

          }
        }
      },
      pagination: {
        total: 'In total',
        item: 'item'
      },
    },
  },
})

export const t = i18n.global.t

export default i18n