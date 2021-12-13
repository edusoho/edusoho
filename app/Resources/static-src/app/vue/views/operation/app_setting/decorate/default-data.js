const DefaultData = {
  slide_show: {
    type: 'slide_show',
    moduleType: '',
    data: []
  },
  course_list: {
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
      items: []
    }
  },
  classroom_list: {
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
      items: []
    }
  },
  poster: {
    type: 'poster',
    moduleType: '',
    data: {
      image: {},
      link: {
        type: '',
        target: {},
        url: ''
      },
      responsive: '1'
    }
  },
  graphic_navigation: {
    type: 'graphic_navigation',
    moduleType: '',
    data: [
      {
        title: '公开课',
        image: {
          url: '',
          icon: 'icon-check-circle'
        },
        link: {
          type: 'openCourse',
          target: '跳转公开课“全部”列表',
          url: ''
        }
      },
      {
        title: '热门课',
        image: {
          url: '',
          icon: 'icon-check-circle'
        },
        link: {
          type: 'course',
          target: '跳转课程“全部”列表',
          url: ''
        }
      },
      {
        title: '热销班',
        image: {
          url: '',
          icon: 'icon-check-circle'
        },
        link: {
          type: 'classroom',
          target: '跳转班级“全部”列表',
          url: ''
        }
      }
    ]
  },
  open_course_list: {
    type: 'open_course_list',
    moduleType: '',
    data: {
      title: '',
      sourceType: 'condition',
      categoryId: '0',
      limitDays: '0',
      limit: '4',
      displayStyle: 'distichous',
      items: []
    }
  },
  item_bank_exercise: {
    type: 'item_bank_exercise',
    moduleType: '',
    data: {
      title: '',
      sourceType: 'condition',
      categoryId: '',
      sort: 'recommendedSeq',
      lastDays: '0',
      limit: '4',
      displayStyle: 'distichous',
      items: []
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

export {
  DefaultData
};
