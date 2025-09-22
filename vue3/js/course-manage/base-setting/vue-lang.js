import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      btn: {
        save: '保存',
      },
      validate: {
        cannotContainAngleBrackets: '标题不能包含尖括号',
        maxByteLimit: '输入内容的长度不能超过 {maxByte} 字节',
        minByteLimit: '输入内容的长度不能少于 {minByte} 字节',
        courseTitleLengthLimit: '字符长度必须小于等于200，一个中文字算2个字符',
        imgTypeLimit: '请上传jpg,gif,png格式的图片',
        imgSizeLimit: '图片大小不能超过2MB',
      },
      label: {
        nothing: '无',
        NonSerialCourse: '非连载课程',
        updating: '更新中',
        completed: '已完结',
      }
    },

    en: {
      btn: {
        save: 'Save',
      },
      validate: {
        cannotContainAngleBrackets: 'Title cannot contain angle brackets',
        maxByteLimit: 'Length of input content cannot exceed {maxByte} characters.',
        minByteLimit: 'The length of the input content must be no less than {minByte} bytes.',
        courseTitleLengthLimit: 'The character length must be less than or equal to 200. One Chinese character is counted as 2 characters.',
        imgTypeLimit: 'Please upload pictures in JPG, GIF or PNG formats.',
        imgSizeLimit: 'The size of the image cannot exceed 2MB.',
      },
      label: {
        nothing: 'Nothing',
        NonSerialCourse: 'Non-serial course',
        updating: 'Updating',
        completed: 'Completed',
      }
    }
  },
})

export const t = i18n.global.t

export default i18n