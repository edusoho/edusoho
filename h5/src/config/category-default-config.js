const CATEGORY_DEFAULT = {
  course_list: [
    {
      data: [],
      moduleType: 'tree',
      text: '分类',
      type: 'category'
    },
    {
      data: [
        { text: '全部', type: 'all' },
        { text: '课程', type: 'normal' },
        { text: '直播', type: 'live' }
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'courseType'
    },
    {
      data: [
        { text: '推荐', type: 'recommendedSeq' },
        { text: '热门', type: '-studentNum' },
        { text: '最新', type: '-createdTime' }
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'sort'
    }
  ],
  classroom_list: [
    {
      data: [],
      moduleType: 'tree',
      text: '分类',
      type: 'category'
    },
    {},
    {
      data: [
        { text: '推荐', type: 'recommendedSeq' },
        { text: '热门', type: '-studentNum' },
        { text: '最新', type: '-createdTime' }
      ],
      moduleType: 'normal',
      text: '课程类型',
      type: 'sort'
    }
  ]
};

export default CATEGORY_DEFAULT;
