import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {

    },




    en: {

    },
  },
})

export const t = i18n.global.t

export default i18n