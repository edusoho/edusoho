import openCourse from './openCourse/index';
import itemBankCourse from './itemBankCourse/index';
import classroom from './classroom/index';
import setting from './setting/index';
import course from './course/index';
import lesson from './lesson/index';
import order from './order/index';
import me from './me/index';
import coupon from './coupon/index';
import vip from './vip/index';
import marketing from './marketing/index';
import studyCard from './study-card/index';
import distribution from './distribution/index';
import liveTimetable from './liveTimetable/index';
import goods from './goods/index';
import reviews from './reviews/index';
import certificate from './certificate/index';
import itemBank from './itemBank/index';
import infoCollection from './infoCollection/index';
import wrongQuestionBook from './wrong-question-book/index';

const config = [
  ...openCourse,
  ...classroom,
  ...course,
  ...lesson,
  ...me,
  ...order,
  ...setting,
  ...coupon,
  ...vip,
  ...marketing,
  ...distribution,
  ...studyCard,
  ...liveTimetable,
  ...goods,
  ...reviews,
  ...itemBankCourse,
  ...certificate,
  ...itemBank,
  ...infoCollection,
  ...wrongQuestionBook,
];

export default config;
