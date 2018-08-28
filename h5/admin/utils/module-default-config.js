export default {
  slideShow: {
    type: 'slide_show',
    moduleType: '',
    data: [{
      title: '',
      image: {},
      link: {
        type: 'url',
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
      sort: '-studentNum',
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
        type: 'url',
        target: null,
        url: ''
      }
    }
  }
}
