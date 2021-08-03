module.exports = [
  // test
  {
    url: '/test',
    type: 'get',
    response: {
      group_number_limit: "100",
      assistant_service_limit: "250",
      review_time_limit: "24"
    }

  },
  {
    url: '/aaa',
    type: 'get',
    response: [{
        id: '3',
        name: 'lalala'
      },
      {
        id: '4',
        name: 'nanan'
      }
    ]

  }
]