/**
 * 使用说明
 * 
 * import Api from 'common/api';
 * 
 * Api.course.create({
 *    params: {},
 *    data: {}
 * }).then((res) => {
 *    // 请求成功
 *    console.log('res', res);
 * }).catch((res) => {
 *   // 异常捕获
 *   console.log('catch', res.responseJSON.error.message);
 * })
 */

import courseModule from './modules/course';
import classroomModule from './modules/classroom';
import tradeModule from './modules/trade';
import captchaModule from './modules/captcha';
import smsModule from './modules/sms';
import taskModule from './modules/task';
import courseTaskModule from './modules/courseTask';

const API_URL_PREFIX = '/api';

const Api = {
  // 课程模块
  course: courseModule(API_URL_PREFIX),
  // 班级模块
  classroom: classroomModule(API_URL_PREFIX),
  trade: tradeModule(API_URL_PREFIX),
  captcha: captchaModule(API_URL_PREFIX),
  sms: smsModule(API_URL_PREFIX),
  task: taskModule(API_URL_PREFIX),
  courseTask: courseTaskModule(API_URL_PREFIX),
};

export default Api;

