import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      title: {
        chapterExercises: '章节练习',
        testPaperPractice: '试卷练习',
      },
      btn: {
        check: '查看'
      }
    },
    en: {
      title: {
        chapterExercises: 'Chapter Exercises',
        testPaperPractice: 'Test Paper Practice',
      },
      btn: {
        check: 'Check'
      }
    }
  }
})

export const t = i18n.global.t
export default i18n