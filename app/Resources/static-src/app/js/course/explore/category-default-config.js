const CATEGORY_DEFAULT = {
  course_list: [
    {
      data: [],
      moduleType: 'tree',
      text: '分类',
      type: 'category',
    },
    {
      data: [
        { text: '全部', type: 'all' },
        { text: '课程', type: 'normal' },
        { text: '直播', type: 'live' },
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'courseType',
    },
    {
      data: [
        { text: '推荐', type: 'recommendedSeq' },
        { text: '热门', type: '-studentNum' },
        { text: '最新', type: '-createdTime' },
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'sort',
    },
  ],
  classroom_list: [
    {
      data: [],
      moduleType: 'tree',
      text: '分类',
      type: 'category',
    },
    {},
    {
      data: [
        { text: '推荐', type: 'recommendedSeq' },
        { text: '热门', type: '-studentNum' },
        { text: '最新', type: '-createdTime' },
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'sort',
    },
  ],
  openCourse_list: [],
  itemBank_list: [
    {
      data: [],
      moduleType: 'tree',
      text: '分类',
      type: 'category',
    },
    {
      data: '',
      moduleType: 'normal',
      text: '题库类型',
      type: 'courseType',
    },
    {
      data: [
        { text: '推荐', type: 'recommendedSeq' },
        { text: '热门', type: '-studentNum' },
        { text: '最新', type: '-createdTime' },
      ],
      moduleType: 'normal',
      text: '题库类型',
      type: 'sort',
    },
  ],
  new_course_list: [
    {
      type: 'type',
      value: 'all',
      options: [
        { text: '课程类型', value: 'all', i18n: true },
        { text: '课程', value: 'normal', i18n: true },
        { text: '直播', value: 'live', i18n: true },
      ],
    },
    {
      type: 'vipLevelId',
      value: '0',
      options: [],
    },
    {
      type: 'sort',
      value: 'recommendedSeq',
      options: [
        { text: '推荐', value: 'recommendedSeq', i18n: true },
        { text: '热门', value: '-studentNum', i18n: true },
        { text: '最新', value: '-createdTime', i18n: true },
      ],
    },
  ],
  new_classroom_list: [
    {
      type: 'vipLevelId',
      value: '0',
      options: [],
    },
    {
      type: 'sort',
      value: 'recommendedSeq',
      options: [
        { text: '推荐', value: 'recommendedSeq', i18n: true },
        { text: '热门', value: '-studentNum', i18n: true },
        { text: '最新', value: '-createdTime', i18n: true },
      ],
    },
  ],
};

export default CATEGORY_DEFAULT;
