const MODULE_DEFAULT = {
  slideShow: {
    type: 'slide_show',
    moduleType: '',
    data: [{
      title: '',
      image: {},
      link: {
        type: 'course',
        target: null,
        url: ''
      }
    }]
  },
  courseList: {
    type: 'course_list',
    moduleType: '',
    data: {
      title: '',
      sourceType: 'condition',
      categoryId: '0',
      sort: 'recommendedSeq',
      lastDays: '0',
      limit: '4',
      items: []
    }
  },
  poster: {
    type: 'poster',
    moduleType: '',
    data: {
      image: {},
      link: {
        type: 'course',
        target: {},
        url: ''
      },
      responsive: '1'
    }
  },
  groupon: {
    type: 'groupon',
    moduleType: '',
    data: {
      activity: {},
      tag: ''
    }
  }
};

const BASE_MODULE = [
  {
    name: '轮播图',
    default: MODULE_DEFAULT.slideShow,
  }, {
    name: '课程列表',
    default: MODULE_DEFAULT.courseList,
  }, {
    name: '图片广告',
    default: MODULE_DEFAULT.poster,
  }
];

const MARKETING_MODULE = [
  {
    name: '拼团活动',
    default: MODULE_DEFAULT.groupon,
  }
]

export { MODULE_DEFAULT, BASE_MODULE, MARKETING_MODULE };
