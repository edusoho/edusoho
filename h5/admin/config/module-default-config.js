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
  classList: {
    type: 'classroom_list',
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
      tag: '',
      titleShow: 'show'
    }
  },
  cut: {
    type: 'cut',
    moduleType: '',
    data: {
      activity: {},
      tag: '',
      titleShow: 'show'
    }
  },
  seckill: {
    type: 'seckill',
    moduleType: '',
    data: {
      activity: {},
      tag: '',
      titleShow: 'show'
    }
  },
  coupon: {
    type: 'coupon',
    moduleType: '',
    data: {
      items: [],
      titleShow: 'show'
    }
  },
  vip: {
    type: 'vip',
    moduleType: '',
    data: {
      items: [],
      sort: 'asc',
      title: '',
      titleShow: 'show'
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
  }, {
    name: '优惠券',
    default: MODULE_DEFAULT.coupon,
  }, {
    name: '会员专区',
    default: MODULE_DEFAULT.vip,
  }
];

const MARKETING_MODULE = [
  {
    name: '拼团活动',
    default: MODULE_DEFAULT.groupon,
  }, {
    name: '砍价',
    default: MODULE_DEFAULT.cut,
  }, {
    name: '秒杀',
    default: MODULE_DEFAULT.seckill,
  },
]

const VALUE_DEFAULT = {
  classroom_list: {
    key: 'title'
  },
  course_list: {
    key: 'displayedTitle'
  },
  groupon: {
    key: 'name'
  },
  coupon: {
    key: 'name'
  },
  cut: {
    key: 'name'
  },
  seckill: {
    key: 'name'
  }
}

const TYPE_TEXT_DEFAULT = {
  course_list: {
    text: '课程'
  },
  classroom_list: {
    text: '班级'
  },
  groupon: {
    text: '活动'
  },
  coupon: {
    text: '优惠券'
  },
  cut: {
    text: '砍价活动'
  },
  seckill: {
    text: '秒杀活动'
  }
}

export { MODULE_DEFAULT, BASE_MODULE, MARKETING_MODULE, VALUE_DEFAULT, TYPE_TEXT_DEFAULT };
