export default {
  slideShow: Object.assign({}, {
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
  }),
  courseList: Object.assign({}, {
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
  }),
  poster: Object.assign({}, {
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
  })
}
