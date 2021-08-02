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
      type: 'categoryId',
      value: '0',
      options: [],
    },
    {
      type: 'type',
      value: 'all',
      options: [
        { text: 'more.all', value: 'all', i18n: true },
        { text: 'more.course', value: 'normal', i18n: true },
        { text: 'more.live', value: 'live', i18n: true },
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
        { text: 'more.recommendation', value: 'recommendedSeq', i18n: true },
        { text: 'more.popular', value: '-studentNum', i18n: true },
        { text: 'more.latest', value: '-createdTime', i18n: true },
      ],
    },
  ],
  new_classroom_list: [
    {
      type: 'categoryId',
      value: '0',
      options: [],
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
        { text: 'more.recommendation', value: 'recommendedSeq', i18n: true },
        { text: 'more.popular', value: '-studentNum', i18n: true },
        { text: 'more.latest', value: '-createdTime', i18n: true },
      ],
    },
  ],
};

export default CATEGORY_DEFAULT;
