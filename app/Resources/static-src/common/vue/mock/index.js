const Mock = require('mockjs');

const test = require('./class-course/index.js');

const mocks = [
  ...test
];

for (const i of mocks) {
  Mock.mock(i.url, i.type || 'get', i.response)
}
