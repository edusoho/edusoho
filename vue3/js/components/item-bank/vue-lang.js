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
        check: '查看',
        bind: '绑定题库',
      },
      label: {
        noQuestionBank: '暂无绑定题库',
        management: '题库练习管理',
        loading: '加载中...',
      },
      tooltip: {
        course: '绑定后课程学员自动加入题库练习，学员在课程内的学习有效期和学习权限同步影响其在题库练习内的有效期和答题权限。',
        classroom: '绑定后班级学员可同步加入到题库练习内（包括在绑定前加入班级的学员），班级学员获得绑定的题库练习的学员权限。',
      },
    },
    en: {
      title: {
        chapterExercises: 'Chapter Exercises',
        testPaperPractice: 'Test Paper Practice',
      },
      btn: {
        check: 'Check',
        bind: 'Bind the question bank',
      },
      label: {
        noQuestionBank: 'No bound question bank yet',
        management: 'Question bank practice management',
        loading: 'Loading...',
      },
      tooltip: {
        course: 'After binding, the course participants will automatically be included in the question bank for practice. The validity period and learning rights within the course will synchronously affect the validity period and answering rights in the question bank practice.',
        classroom: 'After binding, the class members can simultaneously join the question bank practice (including those who joined the class before the binding). The class members will obtain the member privileges of the bound question bank practice.',
      },
    }
  }
})

export const t = i18n.global.t
export default i18n