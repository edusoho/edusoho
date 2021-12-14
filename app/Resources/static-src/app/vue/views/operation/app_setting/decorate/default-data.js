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
        title: '分类课',
        image: { url: '/static-dist/app/img/vue/decorate/gn_classification.png' },
        link: { type: 'openCourse' }
      },
      {
        title: '免费课',
        image: { url: '/static-dist/app/img/vue/decorate/gn_free.png' },
        link: { type: 'course' }
      },
      {
        title: '热门班',
        image: { url: '/static-dist/app/img/vue/decorate/gn_hot.png' },
        link: { type: 'classroom' }
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
