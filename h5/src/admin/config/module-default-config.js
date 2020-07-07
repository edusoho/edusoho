const { protocol, pathname, host } = window.location;
const baseUri = `${protocol}//${host}${pathname
  .split('/')
  .slice(0, -1)
  .join('/')}/`;
// 模块初始化数据
const MODULE_DEFAULT = {
  slideShow: {
    type: 'slide_show',
    moduleType: '',
    data: [
      {
        title: '',
        image: {},
        link: {
          type: 'course',
          target: null,
          url: '',
        },
      },
    ],
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
      displayStyle: 'distichous',
      items: [],
    },
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
      displayStyle: 'distichous',
      items: [],
    },
  },
  openCourseList: {
    type: 'open_course_list',
    moduleType: '',
    data: {
      title: '',
      sourceType: 'condition',
      categoryId: '0',
      limitDays: '0',
      limit: '4',
      displayStyle: 'distichous',
      items: [],
    },
  },
  graphicNavigation: {
    type: 'graphic_navigation',
    moduleType: 'navigation-1',
    data: [
      {
        title: '公开课',
        image: {
          url: '',
          uri: `${baseUri}static/images/openCourse.png`,
        },
        link: {
          type: 'openCourse',
          target: '跳转公开课“全部”列表',
          url: '',
        },
      },
      {
        title: '热门课',
        image: {
          url: '',
          uri: `${baseUri}static/images/hotcourse.png`,
        },
        link: {
          type: 'course',
          target: '跳转课程“全部”列表',
          url: '',
        },
      },
      {
        title: '热销班',
        image: {
          url: '',
          uri: `${baseUri}static/images/hotclass.png`,
        },
        link: {
          type: 'classroom',
          target: '跳转班级“全部”列表',
          url: '',
        },
      },
    ],
  },
  h5GraphicNavigation: {
    type: 'graphic_navigation',
    moduleType: 'navigation-1',
    data: [
      {
        title: '免费课',
        image: {
          url: '',
          uri: `${baseUri}static/images/openCourse.png`,
        },
        link: {
          type: 'course',
          target: '跳转课程“全部”列表',
          url: '',
        },
      },
      {
        title: '热门课',
        image: {
          url: '',
          uri: `${baseUri}static/images/hotcourse.png`,
        },
        link: {
          type: 'course',
          target: '跳转课程“全部”列表',
          url: '',
        },
      },
      {
        title: '热销班',
        image: {
          url: '',
          uri: `${baseUri}static/images/hotclass.png`,
        },
        link: {
          type: 'classroom',
          target: '跳转班级“全部”列表',
          url: '',
        },
      },
    ],
  },
  poster: {
    type: 'poster',
    moduleType: '',
    data: {
      image: {},
      link: {
        type: 'course',
        target: {},
        url: '',
      },
      responsive: '1',
    },
  },
  groupon: {
    type: 'groupon',
    moduleType: '',
    data: {
      activity: {},
      tag: '',
      titleShow: 'show',
    },
  },
  cut: {
    type: 'cut',
    moduleType: '',
    data: {
      activity: {},
      tag: '',
      titleShow: 'show',
    },
  },
  seckill: {
    type: 'seckill',
    moduleType: '',
    data: {
      activity: {},
      tag: '',
      titleShow: 'show',
    },
  },
  coupon: {
    type: 'coupon',
    moduleType: '',
    data: {
      items: [],
      titleShow: 'show',
    },
  },
  vip: {
    type: 'vip',
    moduleType: '',
    data: {
      items: [],
      sort: 'asc',
      title: '',
      titleShow: 'show',
    },
  },
  search: {
    type: 'search',
    moduleType: '',
    data: {},
  },
};
// 各端对应的组件
const BASE_MODULE = [
  {
    name: '轮播图',
    default: MODULE_DEFAULT.slideShow,
  },
  {
    name: '课程列表',
    default: MODULE_DEFAULT.courseList,
  },
  {
    name: '班级列表',
    default: MODULE_DEFAULT.classList,
  },
  {
    name: '图片广告',
    default: MODULE_DEFAULT.poster,
  },
  {
    name: '优惠券',
    default: MODULE_DEFAULT.coupon,
  },
  {
    name: '会员专区',
    default: MODULE_DEFAULT.vip,
  },
];

const MARKETING_MODULE = [
  {
    name: '拼团',
    default: MODULE_DEFAULT.groupon,
  },
  {
    name: '砍价',
    default: MODULE_DEFAULT.cut,
  },
  {
    name: '秒杀',
    default: MODULE_DEFAULT.seckill,
  },
];

const APP_BASE_MODULE = [
  {
    name: '轮播图',
    default: MODULE_DEFAULT.slideShow,
  },
  {
    name: '课程列表',
    default: MODULE_DEFAULT.courseList,
  },
  {
    name: '班级列表',
    default: MODULE_DEFAULT.classList,
  },
  {
    name: '图片广告',
    default: MODULE_DEFAULT.poster,
  },
  {
    name: '优惠券',
    default: MODULE_DEFAULT.coupon,
  },
  {
    name: '会员专区',
    default: MODULE_DEFAULT.vip,
  },
  {
    name: '图文导航',
    default: MODULE_DEFAULT.graphicNavigation,
  },
  {
    name: '公开课列表',
    default: MODULE_DEFAULT.openCourseList,
  },
];

const H5_BASE_MODULE = [
  {
    name: '轮播图',
    default: MODULE_DEFAULT.slideShow,
    icon: 'icon-lunbotu',
  },
  {
    name: '课程列表',
    default: MODULE_DEFAULT.courseList,
    icon: 'icon-kechengliebiao',
  },
  {
    name: '班级列表',
    default: MODULE_DEFAULT.classList,
    icon: 'icon-banjiliebiao',
  },
  {
    name: '图片广告',
    default: MODULE_DEFAULT.poster,
    icon: 'icon-tuwenguanggao',
  },
  {
    name: '图文导航',
    default: MODULE_DEFAULT.h5GraphicNavigation,
    icon: 'icon-tuwendaohang',
  },
  {
    name: '搜索',
    default: MODULE_DEFAULT.search,
    icon: 'icon-sousuo',
  },
];

const H5_MARKETING_MODULE = [
  {
    name: '优惠券',
    default: MODULE_DEFAULT.coupon,
    icon: 'icon-youhuiquan',
  },
  {
    name: '会员专区',
    default: MODULE_DEFAULT.vip,
    icon: 'icon-huiyuanzhuanqu',
  },
  {
    name: '拼团',
    default: MODULE_DEFAULT.groupon,
    icon: 'icon-pintuan',
  },
  {
    name: '砍价',
    default: MODULE_DEFAULT.cut,
    icon: 'icon-kanjia',
  },
  {
    name: '秒杀',
    default: MODULE_DEFAULT.seckill,
    icon: 'icon-miaosha',
  },
];

// 内容条件搜索关键字
const VALUE_DEFAULT = {
  classroom_list: {
    key: 'title',
  },
  course_list: {
    key: 'displayedTitle',
  },
  open_course_list: {
    key: 'title',
  },
  groupon: {
    key: 'name',
  },
  coupon: {
    key: 'name',
  },
  cut: {
    key: 'name',
  },
  seckill: {
    key: 'name',
  },
};

// 拖动可调整顺序文案
const TYPE_TEXT_DEFAULT = {
  course_list: {
    text: '课程',
  },
  classroom_list: {
    text: '班级',
  },
  open_course_list: {
    text: '公开课',
  },
  groupon: {
    text: '活动',
  },
  coupon: {
    text: '优惠券',
  },
  cut: {
    text: '活动',
  },
  seckill: {
    text: '活动',
  },
};

export {
  MODULE_DEFAULT,
  BASE_MODULE,
  MARKETING_MODULE,
  APP_BASE_MODULE,
  H5_BASE_MODULE,
  H5_MARKETING_MODULE,
  VALUE_DEFAULT,
  TYPE_TEXT_DEFAULT,
};
