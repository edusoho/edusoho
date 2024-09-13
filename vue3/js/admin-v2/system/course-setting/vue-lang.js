import {createI18n} from 'vue-i18n';

const i18n = createI18n({
  legacy: false,
  locale: window.app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      label: {
        taskWatermark: '任务详情页水印',
        color: '颜色',
        alpha: '透明度',
      },
      radio: {
        open: '开启',
        close: '关闭',
      },
      tip: {
        purpose: '针对网站内容截图泄露，可进行威慑和溯源。',
        setting: '设置水印信息：',
      },
      checkbox: {
        name: '姓名',
        username: '用户名',
        mobile: '手机号码',
      },
      placeholder: {
        customText: '输入自定义文案',
      },
      validate: {
        alpha: {
          required: '请输入透明度',
          positiveInteger: '请输入正整数',
          max: '请输入不大于100的数值',
        },
      },
    },
    en: {
      label: {
        taskWatermark: 'Task page watermark',
        color: 'Color',
        alpha: 'Transparency',
      },
      radio: {
        open: 'Turn on',
        close: 'Close',
      },
      tip: {
        purpose: 'Deterrence and traceability are available for leaked screenshots of website content.',
        setting: 'Watermark Info Setting: ',
      },
      checkbox: {
        name: 'Name',
        username: 'User Name',
        mobile: 'Mobile',
      },
      placeholder: {
        customText: 'Input custom text',
      },
      validate: {
        alpha: {
          required: 'Please enter transparency',
          positiveInteger: 'Please enter a positive integer',
          max: 'Please enter a value not greater than 100',
        },
      },
    }
  },
});

export const t = i18n.global.t;
