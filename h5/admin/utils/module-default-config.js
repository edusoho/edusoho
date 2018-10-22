export default {
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
