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
    type: 'course',
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
  classList: {
    type: 'classroom',
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
    name: '班级列表',
    default: MODULE_DEFAULT.classList,
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

const VALUE_DEFAULT = {
  classroom: {
    key: 'title'
  },
  course: {
    key: 'displayedTitle'
  },
  groupon: {
    key: 'name'
  }
}

const TYPE_TEXT_DEFAULT = {
  course: {
    text: '课程'
  },
  classroom: {
    text: '班级'
  },
  groupon: {
    text: '活动'
  }
}

export { MODULE_DEFAULT, BASE_MODULE, MARKETING_MODULE, VALUE_DEFAULT, TYPE_TEXT_DEFAULT };
