module.exports = [
  // test
  {
    url: '/test',
    type: 'get',
    response: config => {
      return {
        code: 200
      }
    }
  }
]
