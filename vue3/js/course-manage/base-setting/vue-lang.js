import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      btn: {
        save: '保存',
        reselect: '重新选择',
        cancel: '取消',
        saveTheImage: '保存图片',
      },
      validate: {
        cannotContainAngleBrackets: '标题不能包含尖括号',
        maxByteLimit: '输入内容的长度不能超过 {maxByte} 字节',
        minByteLimit: '输入内容的长度不能少于 {minByte} 字节',
        courseTitleLengthLimit: '字符长度必须小于等于200，一个中文字算2个字符',
        imgTypeLimit: '请上传jpg,gif,png格式的图片',
        imgSizeLimit: '图片大小不能超过2MB',
        inputPlanName: '请输入计划名称',
        inputCourseTitle: '请输入课程标题',
        courseSubtitleLimit: '最多支持50个字符',
      },
      label: {
        nothing: '无',
        NonSerialCourse: '非连载课程',
        updating: '更新中',
        completed: '已完结',
        planName: '计划名字',
        subheadingOfThePlan: '计划副标题',
        courseTitle: '课程标题',
        courseSubtitle: '课程副标题',
        tag: '标签',
        category: '分类',
        organization: '组织机构',
        serializeMode: '连载状态',
        coverPicture: '封面图片',
        courseIntroduction: '课程简介',
      },
      title: {
        basicInformation: '基础信息',
        cropThePicture: '裁剪图片'
      },
      placeholder: {
        pleaseSelect: '请选择'
      },
      tip: {
        tag: '用于按标签搜索课程、相关课程的提取等，由网校管理员后台统一管理',
        uploadPictures: '上传图片',
        coverPicture: '请上传jpg, gif, png格式的图片, 建议图片尺寸为 480×270px。建议图片大小不超过2MB。',
        courseIntroduction: '为正常使用IFrame，请在【管理后台】-【系统】-【站点设置】-【安全】-【IFrame白名单】中进行设置'
      }
    },

    en: {
      btn: {
        save: 'Save',
        reselect: 'Reselect',
        cancel: 'Cancel',
        saveTheImage: 'Save the image',
      },
      validate: {
        cannotContainAngleBrackets: 'Title cannot contain angle brackets',
        maxByteLimit: 'Length of input content cannot exceed {maxByte} characters.',
        minByteLimit: 'The length of the input content must be no less than {minByte} bytes.',
        courseTitleLengthLimit: 'The character length must be less than or equal to 200. One Chinese character is counted as 2 characters.',
        imgTypeLimit: 'Please upload pictures in JPG, GIF or PNG formats.',
        imgSizeLimit: 'The size of the image cannot exceed 2MB.',
        inputPlanName: 'Please enter the name of the plan',
        inputCourseTitle: 'Please enter the course title',
        courseSubtitleLimit: 'Maximum support for 50 characters',
      },
      label: {
        nothing: 'Nothing',
        NonSerialCourse: 'Non-serial course',
        updating: 'Updating',
        completed: 'Completed',
        planName: 'Plan name',
        subheadingOfThePlan: 'Subheading of the plan',
        courseTitle: 'Course title',
        courseSubtitle: 'Course subtitle',
        tag: 'Tag',
        category: 'Category',
        organization: 'Organization',
        serializeMode: 'Serialize mode',
        coverPicture: 'Cover picture',
        courseIntroduction: 'Course introduction',
      },
      title: {
        basicInformation: 'Basic information',
        cropThePicture: 'Crop the picture'
      },
      placeholder: {
        pleaseSelect: 'Please select'
      },
      tip: {
        tag: 'It is used for searching courses by tags, extracting related courses, etc. It is uniformly managed by the online school administrator\'s backend',
        uploadPictures: 'Upload pictures',
        coverPicture: 'Please upload pictures in JPG, GIF or PNG formats. It is recommended that the picture size be 480×270 pixels. The recommended size limit for the pictures is no more than 2MB.',
        courseIntroduction: 'To use IFrame normally, please make the settings in the 【Management Backend】 - 【System】 - 【Site Settings】 - 【Security】 - 【IFrame Whitelist】 section.',
      }
    }
  },
})

export const t = i18n.global.t

export default i18n