import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      empty: '暂无题目',
      message: {
        closed: '题库已关闭，无法继续学习',
        expired: '学习有效期已过期，无法继续学习',
      },
      btn: {
        continue: '继续做题',
        report: '查看报告',
        start: '开始做题',
      },
      accuracy: '正确率',
      topic: '题'
    },
    en: {
      empty: 'No title for now',
      message: {
        closed: 'The question bank has been closed. You cannot continue to study.',
        expired: 'The learning validity period has expired and you are unable to continue the learning.',
      },
      btn: {
        continue: 'Proceed',
        report: 'Review',
        start: 'Launch',
      },
      accuracy: 'Accuracy',
      topic: 'Topic'
    }
  }
})

export const t = i18n.global.t
export default i18n