import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      paging: {
        total: '共 {total} 项',
      },
      label: {
        myQuestionBank: '我的题库',
        empty: '暂无题库',
        closed: '已关闭',
        answerRate: '答题率',
        comprehensionRate: '掌握率',
        complimentaryQuestionBank: '赠送的题库',
        learn: '去学习',
      },
    },
    en: {
      paging: {
        total: 'A total of {total} items'
      },
      label: {
        myQuestionBank: 'My question bank',
        empty: 'No question bank yet',
        closed: 'Closed',
        answerRate: 'Answer rate',
        comprehensionRate: 'Comprehension rate',
        complimentaryQuestionBank: 'donate the question bank',
        learn: 'Learn',
      },
    }
  }
})

export const t = i18n.global.t
export default i18n