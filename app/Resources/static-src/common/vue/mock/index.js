import Mock from 'mockjs';

import test from './multi-class/index';

const mocks = [
  ...test
];

for (const i of mocks) {
  Mock.mock(i.url, i.type || 'get', i.response)
}
