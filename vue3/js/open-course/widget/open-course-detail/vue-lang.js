import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      status: {
        comingSoon: '敬请期待',
        playback: '回放',
        Live: '直播中',
        haveNotStarted: '未开始',
        finished: '已结束',
      },
      tab: {
        intro: '简介',
        catalogue: '目录',
        comment: '评论',
      },
      empty: {
        intro: '暂无简介',
        catalogue: '暂无内容',
      },
      loading: '加载中...',
      btn: {
        comingSoon: '敬请期待',
        viewTheReplay: '查看回放',
        viewTheLive: '查看直播'
      },
    },
    en: {
      status: {
        comingSoon: 'Coming Soon',
        playback: 'Playback',
        Live: 'Living',
        haveNotStarted: 'Have Not Started',
        finished: 'Finished',
      },
      tab: {
        intro: 'Intro',
        catalogue: 'Catalogue',
        comment: 'Comment',
      },
      empty: {
        intro: 'No introduction yet.',
        catalogue: 'No content for now.',
      },
      loading: 'Loading...',
      btn: {
        comingSoon: 'Coming Soon',
        viewTheReplay: 'View The Replay',
        viewTheLive: 'View The Live'
      },
    },
  }
})

export const t = i18n.global.t
export default i18n