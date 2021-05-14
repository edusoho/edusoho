const Mock = require('mockjs');

const test = require('./class-course/test.js');

const mocks = [
  ...test
];

// function mockXHR() {
//   // mock patch
//   // https://github.com/nuysoft/Mock/issues/300
//   Mock.XHR.prototype.proxy_send = Mock.XHR.prototype.send
//   Mock.XHR.prototype.send = function() {
//     if (this.custom.xhr) {
//       this.custom.xhr.withCredentials = this.withCredentials || false

//       if (this.responseType) {
//         this.custom.xhr.responseType = this.responseType
//       }
//     }
//     this.proxy_send(...arguments)
//   }

//   function XHR2ExpressReqWrap(respond) {
//     return function(options) {
//       let result = null
//       if (respond instanceof Function) {
//         const { body, type, url } = options
//         // https://expressjs.com/en/4x/api.html#req
//         result = respond({
//           method: type,
//           body: JSON.parse(body)
//         })
//       } else {
//         result = respond
//       }
//       return Mock.mock(result)
//     }
//   }

  for (const i of mocks) {
    Mock.mock(i.url, i.type || 'get', i.response)
  }
// }

// module.exports = {
//   mocks,
//   mockXHR
// }
